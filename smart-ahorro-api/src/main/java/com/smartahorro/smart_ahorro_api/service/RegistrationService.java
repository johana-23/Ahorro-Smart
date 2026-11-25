package com.smartahorro.smart_ahorro_api.service;

import com.smartahorro.smart_ahorro_api.dto.RegisterRequest;
import com.smartahorro.smart_ahorro_api.entity.User;
import com.smartahorro.smart_ahorro_api.repository.UserRepository;
import org.springframework.dao.DataIntegrityViolationException;
import org.springframework.security.crypto.password.PasswordEncoder;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;
import org.springframework.web.server.ResponseStatusException;
import org.springframework.http.HttpStatus;

@Service
public class RegistrationService {
    private final UserRepository users;
    private final PasswordEncoder encoder;
    public RegistrationService(UserRepository users, PasswordEncoder encoder) {
        this.users = users;
        this.encoder = encoder;
    }
    @Transactional
    public Long register(RegisterRequest request) {
        String email = request.email().trim().toLowerCase(java.util.Locale.ROOT);
        if (users.findByEmail(email).isPresent()) {
            throw new ResponseStatusException(HttpStatus.CONFLICT, "Correo ya registrado");
        }
        try {
            return users.saveAndFlush(new User(request.name().trim(), email,
                    encoder.encode(request.password()))).getId();
        } catch (DataIntegrityViolationException e) {
            throw new ResponseStatusException(HttpStatus.CONFLICT, "No fue posible registrar el usuario", e);
        }
    }
}
