package com.smartahorro.smart_ahorro_api.service;

import org.springframework.jdbc.core.JdbcTemplate;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

/** Estado persistente de los JWT emitidos. Un JWT sin registro activo no es aceptado. */
@Service
public class TokenRevocationService {
    private final JdbcTemplate jdbc;
    public TokenRevocationService(JdbcTemplate jdbc) { this.jdbc = jdbc; }

    public boolean isActive(String jti, long userId) {
        if (jti == null || jti.isBlank() || userId <= 0) return false;
        Integer count = jdbc.queryForObject("""
                SELECT COUNT(*) FROM refresh_tokens
                WHERE access_jti = ? AND user_id = ? AND consumed_at IS NULL
                  AND revoked_at IS NULL AND access_expires_at > UTC_TIMESTAMP(6)
                  AND expires_at > UTC_TIMESTAMP(6)
                """, Integer.class, jti, userId);
        return count != null && count == 1;
    }

    @Transactional
    public void revokeFamily(String familyId) {
        jdbc.update("UPDATE refresh_tokens SET revoked_at = UTC_TIMESTAMP(6) WHERE family_id = ? AND revoked_at IS NULL", familyId);
    }

    @Transactional
    public void revokeUser(long userId) {
        jdbc.update("UPDATE refresh_tokens SET revoked_at = UTC_TIMESTAMP(6) WHERE user_id = ? AND revoked_at IS NULL", userId);
    }
}
