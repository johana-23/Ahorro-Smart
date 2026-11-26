package com.smartahorro.smart_ahorro_api.service;

import com.smartahorro.smart_ahorro_api.dto.LoginResponse;
import com.smartahorro.smart_ahorro_api.dto.SessionResponse;
import com.smartahorro.smart_ahorro_api.repository.UserRepository;
import java.nio.charset.StandardCharsets;
import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;
import java.security.SecureRandom;
import java.time.Duration;
import java.time.LocalDateTime;
import java.time.ZoneOffset;
import java.util.Base64;
import java.util.HexFormat;
import java.util.List;
import java.util.UUID;
import org.springframework.jdbc.core.JdbcTemplate;
import org.springframework.security.authentication.BadCredentialsException;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

/** Coordina el JWT existente y la rotación de refresh tokens opacos. */
@Service
public class AuthSessionService {
    private static final SecureRandom RANDOM = new SecureRandom();
    private static final long SESSION_DAYS = 7;
    private final AuthService auth;
    private final JwtService jwt;
    private final UserRepository users;
    private final JdbcTemplate jdbc;
    private final TokenRevocationService revocations;

    public AuthSessionService(AuthService auth, JwtService jwt, UserRepository users,
                              JdbcTemplate jdbc, TokenRevocationService revocations) {
        this.auth = auth; this.jwt = jwt; this.users = users;
        this.jdbc = jdbc; this.revocations = revocations;
    }

    @Transactional
    public SessionResponse login(String email, String password) {
        // AuthService bloquea la fila del usuario hasta terminar esta transacción.
        long userId = auth.authenticate(email, password);
        return issue(userId, UUID.randomUUID().toString(), now().plusDays(SESSION_DAYS));
    }

    // La revocación por reutilización debe confirmarse aunque se responda HTTP 401.
    @Transactional(noRollbackFor = BadCredentialsException.class)
    public SessionResponse refresh(String rawToken) {
        String hash = hash(rawToken);
        TokenRow row = lockToken(hash);
        if (row == null || row.revoked() || !row.expiresAt().isAfter(now())) throw invalid();
        if (row.consumed()) {
            revocations.revokeFamily(row.family());
            throw invalid();
        }
        // Evitar emitir un JWT de 15 min cuando queda menos tiempo de sesión.
        if (Duration.between(now(), row.expiresAt()).getSeconds() < 900) {
            revocations.revokeFamily(row.family());
            throw invalid();
        }
        jdbc.update("UPDATE refresh_tokens SET consumed_at = UTC_TIMESTAMP(6) WHERE token_hash = ?", hash);
        return issue(row.userId(), row.family(), row.expiresAt());
    }

    @Transactional
    public void logout(String rawToken) {
        TokenRow row = lockToken(hash(rawToken));
        if (row != null) revocations.revokeFamily(row.family());
    }

    private TokenRow lockToken(String hash) {
        List<Long> ids = jdbc.query("SELECT user_id FROM refresh_tokens WHERE token_hash = ?",
                (rs, i) -> rs.getLong(1), hash);
        if (ids.isEmpty()) return null;
        // Mismo orden de bloqueo que login y restablecimiento de contraseña.
        if (users.findByIdForUpdate(ids.getFirst()).isEmpty()) return null;
        List<TokenRow> rows = jdbc.query("""
                SELECT user_id, family_id, expires_at, consumed_at, revoked_at
                FROM refresh_tokens WHERE token_hash = ? FOR UPDATE
                """, (rs, i) -> new TokenRow(rs.getLong("user_id"), rs.getString("family_id"),
                rs.getObject("expires_at", LocalDateTime.class),
                rs.getObject("consumed_at") != null, rs.getObject("revoked_at") != null), hash);
        return rows.isEmpty() ? null : rows.getFirst();
    }

    private SessionResponse issue(long userId, String family, LocalDateTime expiresAt) {
        String jti = UUID.randomUUID().toString();
        byte[] bytes = new byte[32]; RANDOM.nextBytes(bytes);
        String refresh = Base64.getUrlEncoder().withoutPadding().encodeToString(bytes);
        LocalDateTime issued = now();
        LoginResponse access = jwt.issueAccessToken(userId, jti);
        jdbc.update("""
                INSERT INTO refresh_tokens(token_hash, family_id, user_id, access_jti,
                  access_expires_at, expires_at, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?)
                """, hash(refresh), family, userId, jti, issued.plusSeconds(access.expiresIn()), expiresAt, issued);
        return new SessionResponse(access.accessToken(), access.tokenType(), access.expiresIn(),
                refresh, Math.max(0, Duration.between(issued, expiresAt).getSeconds()));
    }

    static String hash(String value) {
        try {
            return HexFormat.of().formatHex(MessageDigest.getInstance("SHA-256")
                    .digest(value.getBytes(StandardCharsets.UTF_8)));
        } catch (NoSuchAlgorithmException e) { throw new IllegalStateException(e); }
    }
    private static LocalDateTime now() { return LocalDateTime.now(ZoneOffset.UTC); }
    private static BadCredentialsException invalid() { return new BadCredentialsException("Sesion invalida"); }
    private record TokenRow(long userId, String family, LocalDateTime expiresAt, boolean consumed, boolean revoked) {}
}
