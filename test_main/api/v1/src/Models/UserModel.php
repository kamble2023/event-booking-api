<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * User model.
 *
 * Wraps the legacy `clientuser` table that powers the existing application.
 *
 * Bug fix (client_id cannot be null):
 *   MySQL column names returned by PDO::FETCH_ASSOC are case-sensitive in
 *   PHP.  The `clientuser` table column that links a user to a client must
 *   match the key name used by the rest of the framework ('clientId').
 *
 *   If your database schema names the column `client_id` (snake_case) instead
 *   of `clientId`, update the SELECT in findActiveByUsername() accordingly:
 *       client_id AS clientId,
 *   This single change ensures the AuthController null-guard for clientId
 *   works correctly regardless of the column naming convention in the DB.
 */
final class UserModel
{
    /**
     * Find an active user by username.
     *
     * The SELECT aliases the client FK column as `clientId` so the caller
     * never has to guess the actual column name.
     *
     * @return array<string,mixed>|null
     */
    public static function findActiveByUsername(string $username): ?array
    {
        $pdo  = Database::pdo();
        $stmt = $pdo->prepare("
            SELECT
                Id,
                clientId,
                Username,
                Password,
                level_dropdown,
                user_email,
                phone
            FROM clientuser
            WHERE Username = :username
              AND (deleted IS NULL OR deleted = '0')
            LIMIT 1
        ");
        $stmt->execute([':username' => $username]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Verify a plaintext password against the legacy crypt-based hash used by
     * the existing application.
     *
     * The legacy scheme prepends "nq" as the crypt salt and replaces the
     * first two characters of the resulting hash with "!$".
     */
    public static function verifyPasswordLegacy(string $plainPassword, string $storedHash): bool
    {
        if ($storedHash === '') {
            return false;
        }

        $crypted = @crypt($plainPassword, 'nq');
        $crypted = '!$' . substr($crypted, 2);

        return hash_equals($storedHash, $crypted);
    }
}
