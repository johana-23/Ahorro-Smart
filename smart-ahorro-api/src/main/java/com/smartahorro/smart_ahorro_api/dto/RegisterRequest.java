package com.smartahorro.smart_ahorro_api.dto;

import jakarta.validation.constraints.Email;
import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.Size;

public record RegisterRequest(
    @NotBlank @Size(min = 4, max = 100) String name,
    @NotBlank @Email @Size(max = 150) String email,
    @NotBlank @Size(min = 8, max = 72) String password
) {
    @jakarta.validation.constraints.AssertTrue(message = "La contrasena no puede superar 72 bytes UTF-8")
    public boolean isPasswordByteLengthValid() {
        return password == null || password.getBytes(java.nio.charset.StandardCharsets.UTF_8).length <= 72;
    }
}
