package com.smartahorro.smart_ahorro_api.controller;

import org.springframework.http.HttpHeaders;
import org.springframework.http.HttpStatus;
import org.springframework.http.MediaType;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

import com.smartahorro.smart_ahorro_api.dto.ForgotPasswordRequest;
import com.smartahorro.smart_ahorro_api.dto.MessageResponse;
import com.smartahorro.smart_ahorro_api.dto.ResetPasswordRequest;
import com.smartahorro.smart_ahorro_api.service.PasswordResetService;

import jakarta.validation.Valid;

@RestController
@RequestMapping(value = "/api/v1/auth", produces = MediaType.APPLICATION_JSON_VALUE)
public class PasswordResetController {

    private final PasswordResetService service;

    public PasswordResetController(PasswordResetService service) {
        this.service = service;
    }

    @PostMapping(value = "/forgot-password", consumes = MediaType.APPLICATION_JSON_VALUE)
    public ResponseEntity<MessageResponse> forgotPassword(@Valid @RequestBody ForgotPasswordRequest request) {
        service.requestReset(request.email());
        return ResponseEntity.status(HttpStatus.ACCEPTED)
                .header(HttpHeaders.CACHE_CONTROL, "no-store")
                .body(new MessageResponse(
                        "Si el correo esta registrado, recibiras un enlace para restablecer la contrasena"));
    }

    @PostMapping(value = "/reset-password", consumes = MediaType.APPLICATION_JSON_VALUE)
    public ResponseEntity<Void> resetPassword(@Valid @RequestBody ResetPasswordRequest request) {
        service.reset(request.email(), request.token(), request.password());
        return ResponseEntity.noContent()
                .header(HttpHeaders.CACHE_CONTROL, "no-store")
                .build();
    }
}
