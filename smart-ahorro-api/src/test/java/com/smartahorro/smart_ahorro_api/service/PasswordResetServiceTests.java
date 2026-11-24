package com.smartahorro.smart_ahorro_api.service;

import java.time.LocalDateTime;
import java.util.Optional;

import static org.junit.jupiter.api.Assertions.assertThrows;
import org.junit.jupiter.api.BeforeEach;
import org.junit.jupiter.api.Test;
import static org.mockito.ArgumentMatchers.any;
import static org.mockito.ArgumentMatchers.anyString;
import static org.mockito.ArgumentMatchers.eq;
import static org.mockito.Mockito.mock;
import static org.mockito.Mockito.never;
import static org.mockito.Mockito.verify;
import static org.mockito.Mockito.verifyNoInteractions;
import static org.mockito.Mockito.when;
import org.springframework.security.crypto.password.PasswordEncoder;
import org.springframework.web.server.ResponseStatusException;

import com.smartahorro.smart_ahorro_api.entity.PasswordResetToken;
import com.smartahorro.smart_ahorro_api.entity.User;
import com.smartahorro.smart_ahorro_api.repository.PasswordResetTokenRepository;
import com.smartahorro.smart_ahorro_api.repository.UserRepository;

class PasswordResetServiceTests {

    private UserRepository users;
    private PasswordResetTokenRepository tokens;
    private PasswordEncoder encoder;
    private PasswordResetMailer mailer;
    private PasswordResetService service;

    @BeforeEach
    void setUp() {
        users = mock(UserRepository.class);
        tokens = mock(PasswordResetTokenRepository.class);
        encoder = mock(PasswordEncoder.class);
        mailer = mock(PasswordResetMailer.class);
        service = new PasswordResetService(users, tokens, encoder, mailer, 60, 60);
    }

    @Test
    void correoInexistenteNoGeneraTokenNiCorreo() {
        when(users.findByEmail("no@existe.com")).thenReturn(Optional.empty());

        service.requestReset("No@Existe.com");

        verifyNoInteractions(mailer);
        verify(tokens, never()).save(any());
    }

    @Test
    void correoExistenteGeneraTokenHasheadoYEnviaEnlace() {
        when(users.findByEmail("a@b.com")).thenReturn(Optional.of(mock(User.class)));
        when(tokens.findById("a@b.com")).thenReturn(Optional.empty());
        when(encoder.encode(anyString())).thenReturn("hash");

        service.requestReset("A@B.com");

        verify(tokens).save(any(PasswordResetToken.class));
        verify(mailer).sendResetLink(eq("a@b.com"), anyString());
    }

    @Test
    void solicitudRepetidaDentroDelIntervaloSeIgnora() {
        when(users.findByEmail("a@b.com")).thenReturn(Optional.of(mock(User.class)));
        when(tokens.findById("a@b.com")).thenReturn(Optional.of(
                new PasswordResetToken("a@b.com", "hash", LocalDateTime.now())));

        service.requestReset("a@b.com");

        verifyNoInteractions(mailer);
        verify(tokens, never()).save(any());
    }

    @Test
    void tokenValidoCambiaLaContrasenaYSeConsume() {
        User user = mock(User.class);
        PasswordResetToken row = new PasswordResetToken("a@b.com", "hash", LocalDateTime.now());
        when(tokens.findById("a@b.com")).thenReturn(Optional.of(row));
        when(encoder.matches("token-plano", "hash")).thenReturn(true);
        when(encoder.encode("NuevaClave123")).thenReturn("nuevo-hash");
        when(users.findByEmail("a@b.com")).thenReturn(Optional.of(user));

        service.reset("a@b.com", "token-plano", "NuevaClave123");

        verify(user).changePassword("nuevo-hash");
        verify(tokens).delete(row);
    }

    @Test
    void tokenExpiradoSeRechaza() {
        User user = mock(User.class);
        when(tokens.findById("a@b.com")).thenReturn(Optional.of(
                new PasswordResetToken("a@b.com", "hash", LocalDateTime.now().minusMinutes(61))));
        when(encoder.matches(anyString(), anyString())).thenReturn(true);
        when(users.findByEmail("a@b.com")).thenReturn(Optional.of(user));

        assertThrows(ResponseStatusException.class,
                () -> service.reset("a@b.com", "token-plano", "NuevaClave123"));
        verify(user, never()).changePassword(anyString());
    }

    @Test
    void tokenIncorrectoSeRechaza() {
        User user = mock(User.class);
        when(tokens.findById("a@b.com")).thenReturn(Optional.of(
                new PasswordResetToken("a@b.com", "hash", LocalDateTime.now())));
        when(encoder.matches("otro", "hash")).thenReturn(false);
        when(users.findByEmail("a@b.com")).thenReturn(Optional.of(user));

        assertThrows(ResponseStatusException.class,
                () -> service.reset("a@b.com", "otro", "NuevaClave123"));
        verify(user, never()).changePassword(anyString());
    }

    @Test
    void sinTokenSolicitadoSeRechaza() {
        when(tokens.findById("a@b.com")).thenReturn(Optional.empty());

        assertThrows(ResponseStatusException.class,
                () -> service.reset("a@b.com", "x", "NuevaClave123"));
    }
}
