package com.smartahorro.smart_ahorro_api.service;

import java.util.Locale;
import org.springframework.http.HttpStatus;
import org.springframework.jdbc.core.JdbcTemplate;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;
import org.springframework.web.server.ResponseStatusException;

/** Ventanas fijas persistentes, independientes del proxy Laravel y de los reinicios. */
@Service
public class AuthRateLimitService {
    private final JdbcTemplate jdbc;
    public AuthRateLimitService(JdbcTemplate jdbc) { this.jdbc = jdbc; }

    @Transactional(noRollbackFor = ResponseStatusException.class)
    public void check(String operation, String identifier, int maxAttempts, int seconds) {
        // El limite global protege la API incluso con correos aleatorios.
        hit(operation + ":global", 300, 60);
        String normalized = identifier;
        if (operation.equals("login") || operation.equals("register") || operation.equals("forgot") || operation.equals("reset")) {
            normalized = identifier.trim().toLowerCase(Locale.ROOT);
            // La tabla users usa utf8mb4_unicode_ci. Alias equivalentes deben compartir límite.
            normalized = jdbc.queryForObject("SELECT HEX(WEIGHT_STRING(CONVERT(? USING utf8mb4) COLLATE utf8mb4_unicode_ci))",
                    String.class, normalized);
        }
        hit(operation + ":" + normalized, maxAttempts, seconds);
    }

    private void hit(String key, int max, int seconds) {
        String hash = AuthSessionService.hash(key);
        jdbc.update("""
                INSERT INTO auth_rate_limits(bucket_key, attempts, window_until)
                VALUES (?, 1, TIMESTAMPADD(SECOND, ?, UTC_TIMESTAMP(6)))
                ON DUPLICATE KEY UPDATE
                  attempts = IF(window_until <= UTC_TIMESTAMP(6), 1, LEAST(attempts + 1, 1000000)),
                  window_until = IF(window_until <= UTC_TIMESTAMP(6), TIMESTAMPADD(SECOND, ?, UTC_TIMESTAMP(6)), window_until)
                """, hash, seconds, seconds);
        Integer count = jdbc.queryForObject("SELECT attempts FROM auth_rate_limits WHERE bucket_key = ?",
                Integer.class, hash);
        if (count == null || count > max) {
            throw new ResponseStatusException(HttpStatus.TOO_MANY_REQUESTS, "Demasiadas solicitudes");
        }
    }
}
