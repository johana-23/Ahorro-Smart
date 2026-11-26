package com.smartahorro.smart_ahorro_api.dto;

import jakarta.validation.constraints.Email;
import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.Size;

public record ResetPasswordRequest(
        @NotBlank @Email @Size(max = 255) String email,
        @NotBlank @jakarta.validation.constraints.Pattern(regexp = "[A-Za-z0-9_-]{43}") String token,
        @NotBlank @Size(min = 8, max = 72) String password
) {
    @jakarta.validation.constraints.AssertTrue(message = "La contrasena no puede superar 72 bytes UTF-8")
    public boolean isPasswordByteLengthValid() {
        return password == null || password.getBytes(java.nio.charset.StandardCharsets.UTF_8).length <= 72;
    }
}
