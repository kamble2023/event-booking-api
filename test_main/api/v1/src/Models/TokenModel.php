<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Token model — issues, validates, and revokes non-JWT bearer tokens.
 *
 * Only the SHA-256 hash of each raw token is stored in the database so that
 * a compromised database cannot be used to make authenticated requests.
 *
 * Bug fix: client_id is now typed as ?int and an explicit PDO::PARAM_INT
 * binding is used so that the value is never coerced to NULL by PDO when
 * the integer is 0.  The issueToken() caller is responsible for passing a
 * valid, non-null client ID (the AuthController validates this before
 * calling this method).
 */
final class TokenModel
{
    /**
     * Create and persist a new bearer token, returning the raw token string
     * together with its expiry timestamp.
     *
     * @param int      $clientId   ID of the client / tenant.
     * @param int|null $userId     ID of the authenticated user (nullable).
     * @param int      $ttlSeconds Time-to-live in seconds.
     * @return array{token: string, expires_at: string}
     */
    public static function issueToken(int $clientId, ?int $userId, int $ttlSeconds): array
    {
        $token = bin2hex(random_bytes(32)); // 64-char hex bearer token
        $hash  = hash('sha256', $token);   // only the hash is persisted

        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $exp = $now->modify("+{$ttlSeconds} seconds");

        $pdo  = Database::pdo();
        $stmt = $pdo->prepare("
            INSERT INTO api_tokens
                (client_id, user_id, token_hash, status, last_activity, created_at, expires_at)
            VALUES
                (:client_id, :user_id, :token_hash, 'Active', :last_activity, :created_at, :expires_at)
        ");

        // Use explicit PDO type constants to prevent PHP null-coercion bugs
        // where integer 0 could be mis-bound as SQL NULL on certain PDO versions.
        $stmt->bindValue(':client_id',    $clientId,                        \PDO::PARAM_INT);
        $stmt->bindValue(':user_id',      $userId,                          $userId !== null ? \PDO::PARAM_INT : \PDO::PARAM_NULL);
        $stmt->bindValue(':token_hash',   $hash,                            \PDO::PARAM_STR);
        $stmt->bindValue(':last_activity', $now->format('Y-m-d H:i:s'),    \PDO::PARAM_STR);
        $stmt->bindValue(':created_at',   $now->format('Y-m-d H:i:s'),     \PDO::PARAM_STR);
        $stmt->bindValue(':expires_at',   $exp->format('Y-m-d H:i:s'),     \PDO::PARAM_STR);
        $stmt->execute();

        return [
            'token'      => $token,
            'expires_at' => $exp->format(DATE_ATOM),
        ];
    }

    /**
     * Validate a raw bearer token.
     *
     * Returns the token row on success, or null when the token is unknown,
     * revoked, or expired.  Updates last_activity on every successful check.
     *
     * @return array<string,mixed>|null
     */
    public static function validate(string $token): ?array
    {
        $hash = hash('sha256', $token);

        $pdo  = Database::pdo();
        $stmt = $pdo->prepare("
            SELECT *
            FROM   api_tokens
            WHERE  token_hash = :hash
              AND  status     = 'Active'
              AND  expires_at > UTC_TIMESTAMP()
            LIMIT  1
        ");
        $stmt->execute([':hash' => $hash]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        // Update last_activity so callers can detect stale sessions
        $upd = $pdo->prepare("UPDATE api_tokens SET last_activity = UTC_TIMESTAMP() WHERE id = :id");
        $upd->execute([':id' => $row['id']]);

        return $row;
    }

    /**
     * Revoke a bearer token so it can no longer be used.
     */
    public static function revoke(string $token): void
    {
        $hash = hash('sha256', $token);
        $pdo  = Database::pdo();
        $stmt = $pdo->prepare("UPDATE api_tokens SET status = 'Revoked' WHERE token_hash = :hash");
        $stmt->execute([':hash' => $hash]);
    }
}
