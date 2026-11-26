package com.smartahorro.smart_ahorro_api.service;

import java.util.Optional;

import static org.junit.jupiter.api.Assertions.assertEquals;
import static org.junit.jupiter.api.Assertions.assertThrows;
import org.junit.jupiter.api.BeforeEach;
import org.junit.jupiter.api.Test;
import static org.mockito.Mockito.mock;
import static org.mockito.Mockito.verifyNoInteractions;
import static org.mockito.Mockito.when;
import org.springframework.security.authentication.BadCredentialsException;
import org.springframework.security.crypto.password.PasswordEncoder;

import com.smartahorro.smart_ahorro_api.entity.User;
import com.smartahorro.smart_ahorro_api.repository.UserRepository;

class AuthServiceTests {

    private UserRepository userRepository;
    private PasswordEncoder passwordEncoder;
    private AuthService authService;

    @BeforeEach
    void setUp() {
        userRepository = mock(UserRepository.class);
        passwordEncoder = mock(PasswordEncoder.class);

        authService = new AuthService(
                userRepository,
                passwordEncoder
        );
    }

    @Test
    void debeAutenticarCredencialesCorrectas() {

        User user = mock(User.class);

        when(user.getId()).thenReturn(1L);
        when(user.getPassword()).thenReturn("hash-bcrypt");

        when(userRepository.findByEmailForUpdate("usuario@ejemplo.com"))
                .thenReturn(Optional.of(user));

        when(passwordEncoder.matches(
                "ClaveSegura123!",
                "hash-bcrypt"
        )).thenReturn(true);

        Long userId = authService.authenticate(
                "usuario@ejemplo.com",
                "ClaveSegura123!"
        );

        assertEquals(1L, userId);
    }

    @Test
    void debeRechazarContrasenaIncorrecta() {

        User user = mock(User.class);

        when(user.getPassword()).thenReturn("hash-bcrypt");

        when(userRepository.findByEmailForUpdate("usuario@ejemplo.com"))
                .thenReturn(Optional.of(user));

        when(passwordEncoder.matches(
                "ClaveIncorrecta",
                "hash-bcrypt"
        )).thenReturn(false);

        assertThrows(
                BadCredentialsException.class,
                () -> authService.authenticate(
                        "usuario@ejemplo.com",
                        "ClaveIncorrecta"
                )
        );
    }

    @Test
    void debeRechazarCorreoInexistente() {

        when(userRepository.findByEmailForUpdate("noexiste@ejemplo.com"))
                .thenReturn(Optional.empty());

        assertThrows(
                BadCredentialsException.class,
                () -> authService.authenticate(
                        "noexiste@ejemplo.com",
                        "ClaveSegura123!"
                )
        );
    }

    @Test
    void debeRechazarCredencialesVacias() {

        assertThrows(
                BadCredentialsException.class,
                () -> authService.authenticate("", "")
        );

        verifyNoInteractions(userRepository, passwordEncoder);
    }
}