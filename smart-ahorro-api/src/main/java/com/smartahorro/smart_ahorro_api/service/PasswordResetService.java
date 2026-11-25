package com.smartahorro.smart_ahorro_api.service;

import java.security.SecureRandom;
import java.time.LocalDateTime;
import java.util.Base64;
import java.util.Locale;
import java.util.Optional;

import org.springframework.beans.factory.annotation.Value;
import org.springframework.http.HttpStatus;
import org.springframework.security.crypto.password.PasswordEncoder;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;
import org.springframework.web.server.ResponseStatusException;

import com.smartahorro.smart_ahorro_api.entity.PasswordResetToken;
import com.smartahorro.smart_ahorro_api.entity.User;
import com.smartahorro.smart_ahorro_api.repository.PasswordResetTokenRepository;
import com.smartahorro.smart_ahorro_api.repository.UserRepository;

@Service
public class PasswordResetService {

    private static final SecureRandom RANDOM = new SecureRandom();

    private final UserRepository users;
    private final PasswordResetTokenRepository tokens;
    private final PasswordEncoder encoder;
    private final PasswordResetMailer mailer;
    private final long ttlMinutes;
    private final long resendSeconds;

    public PasswordResetService(
            UserRepository users,
            PasswordResetTokenRepository tokens,
            PasswordEncoder encoder,
            PasswordResetMailer mailer,
            @Value("${smart-ahorro.password-reset.ttl-minutes:60}") long ttlMinutes,
            @Value("${smart-ahorro.password-reset.resend-seconds:60}") long resendSeconds
    ) {
        this.users = users;
        this.tokens = tokens;
        this.encoder = encoder;
        this.mailer = mailer;
        this.ttlMinutes = ttlMinutes;
        this.resendSeconds = resendSeconds;
    }

    /**
     * Genera y envia un token si el correo existe. Siempre termina sin error
     * para no revelar si la cuenta existe.
     */
    @Transactional
    public void requestReset(String rawEmail) {
        String email = normalize(rawEmail);

        Optional<User> user = users.findByEmail(email);
        if (user.isEmpty()) {
            return;
        }

        Optional<PasswordResetToken> existing = tokens.findById(email);
        if (existing.isPresent()
                && existing.get().getCreatedAt() != null
                && existing.get().getCreatedAt().plusSeconds(resendSeconds).isAfter(LocalDateTime.now())) {
            return; // solicitud repetida dentro del intervalo minimo
        }

        byte[] bytes = new byte[32];
        RANDOM.nextBytes(bytes);
        String token = Base64.getUrlEncoder().withoutPadding().encodeToString(bytes);

        tokens.save(new PasswordResetToken(email, encoder.encode(token), LocalDateTime.now()));
        mailer.sendResetLink(email, token);
    }

    /**
     * Cambia la contrasena si el token es valido, no ha vencido y no se uso antes.
     */
    @Transactional
    public void reset(String rawEmail, String token, String newPassword) {
        String email = normalize(rawEmail);

        PasswordResetToken stored = tokens.findById(email)
                .orElseThrow(PasswordResetService::invalidToken);

        if (stored.getCreatedAt() == null
                || stored.getCreatedAt().plusMinutes(ttlMinutes).isBefore(LocalDateTime.now())
                || !encoder.matches(token, stored.getToken())) {
            throw invalidToken();
        }

        User user = users.findByEmail(email).orElseThrow(PasswordResetService::invalidToken);

        user.changePassword(encoder.encode(newPassword));
        users.save(user);
        tokens.delete(stored);
    }

    private static String normalize(String email) {
        return email == null ? "" : email.trim().toLowerCase(Locale.ROOT);
    }

    private static ResponseStatusException invalidToken() {
        return new ResponseStatusException(HttpStatus.BAD_REQUEST, "Token de recuperacion invalido o expirado");
    }
}
