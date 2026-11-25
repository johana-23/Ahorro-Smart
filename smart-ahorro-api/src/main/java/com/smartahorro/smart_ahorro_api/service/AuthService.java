package com.smartahorro.smart_ahorro_api.service;

import org.springframework.security.authentication.BadCredentialsException;
import org.springframework.security.crypto.password.PasswordEncoder;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

import com.smartahorro.smart_ahorro_api.entity.User;
import com.smartahorro.smart_ahorro_api.repository.UserRepository;

@Service
public class AuthService {

    private final UserRepository userRepository;
    private final PasswordEncoder passwordEncoder;

    public AuthService(
            UserRepository userRepository,
            PasswordEncoder passwordEncoder
    ) {
        this.userRepository = userRepository;
        this.passwordEncoder = passwordEncoder;
    }

    @Transactional(readOnly = true)
    public Long authenticate(String email, String password) {

        if (email == null || email.isBlank()
                || password == null || password.isBlank()) {
            throw invalidCredentials();
        }

        User user = userRepository.findByEmail(email)
                .orElseThrow(this::invalidCredentials);

        if (!passwordEncoder.matches(
                password,
                user.getPassword()
        )) {
            throw invalidCredentials();
        }

        return user.getId();
    }

    private BadCredentialsException invalidCredentials() {
        return new BadCredentialsException(
                "Credenciales invalidas"
        );
    }
}