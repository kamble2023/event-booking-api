-- =============================================================================
--  api_tokens table — non-JWT bearer token store
-- =============================================================================
--
--  Run this once against your application database before using the auth
--  endpoints.  Only the SHA-256 hash of each bearer token is persisted;
--  the raw token string is returned to the client at issue time and never
--  stored in plain text.
--
--  Columns
--  -------
--  client_id      INT NOT NULL — FK-compatible with your `clients` table.
--                 NOTE: this column must NOT be null.  If a `clientuser` row
--                 has no associated client, the login endpoint returns a 422
--                 rather than attempting the INSERT (which previously caused
--                 the integrity-constraint violation).
--
--  user_id        INT NULL — nullable so the table can be reused for
--                 service-account (client-level) tokens.
--
--  token_hash     CHAR(64) — SHA-256 hex digest of the raw token.
--
--  status         ENUM — 'Active' or 'Revoked'.
--
--  last_activity  DATETIME — updated on every successful validation call.
--
--  expires_at     DATETIME — token is rejected once UTC_TIMESTAMP() passes
--                 this value.
-- =============================================================================

CREATE TABLE IF NOT EXISTS api_tokens (
    id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    client_id     INT             NOT NULL,
    user_id       INT                 NULL,
    token_hash    CHAR(64)        NOT NULL,
    status        ENUM('Active','Revoked') NOT NULL DEFAULT 'Active',
    last_activity DATETIME            NULL,
    created_at    DATETIME        NOT NULL,
    expires_at    DATETIME        NOT NULL,

    PRIMARY KEY (id),
    UNIQUE  KEY uq_token_hash (token_hash),
    KEY         idx_client    (client_id),
    KEY         idx_user      (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
