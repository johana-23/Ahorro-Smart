package com.smartahorro.smart_ahorro_api.entity;

import java.time.LocalDateTime;

import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.Id;
import jakarta.persistence.Table;

/**
 * Tabla tecnica password_reset_tokens (creada por la migracion base de Laravel).
 * Una fila por correo; el token se guarda como hash BCrypt, nunca en claro.
 */
@Entity
@Table(name = "password_reset_tokens")
public class PasswordResetToken {

    @Id
    @Column(name = "email", nullable = false, length = 255)
    private String email;

    @Column(name = "token", nullable = false, length = 255)
    private String token;

    @Column(name = "created_at")
    private LocalDateTime createdAt;

    protected PasswordResetToken() {
        // Constructor requerido por JPA.
    }

    public PasswordResetToken(String email, String tokenHash, LocalDateTime createdAt) {
        this.email = email;
        this.token = tokenHash;
        this.createdAt = createdAt;
    }

    public String getEmail() {
        return email;
    }

    public String getToken() {
        return token;
    }

    public LocalDateTime getCreatedAt() {
        return createdAt;
    }
}
