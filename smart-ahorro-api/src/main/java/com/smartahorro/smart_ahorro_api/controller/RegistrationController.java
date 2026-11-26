package com.smartahorro.smart_ahorro_api.controller;

import com.smartahorro.smart_ahorro_api.dto.RegisterRequest;
import com.smartahorro.smart_ahorro_api.dto.RegisterResponse;
import com.smartahorro.smart_ahorro_api.service.RegistrationService;
import jakarta.validation.Valid;
import com.smartahorro.smart_ahorro_api.service.AuthRateLimitService;
import org.springframework.http.HttpHeaders;
import org.springframework.http.HttpStatus;
import org.springframework.http.MediaType;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

@RestController
@RequestMapping(value = "/api/v1/auth", produces = MediaType.APPLICATION_JSON_VALUE)
public class RegistrationController {
    private final RegistrationService registration;
    private final AuthRateLimitService limits;
    public RegistrationController(RegistrationService registration, AuthRateLimitService limits) { this.registration = registration; this.limits = limits; }
    @PostMapping(value = "/register", consumes = MediaType.APPLICATION_JSON_VALUE)
    public ResponseEntity<RegisterResponse> register(@Valid @RequestBody RegisterRequest request) {
        limits.check("register", request.email(), 5, 900);
        Long id = registration.register(request);
        return ResponseEntity.status(HttpStatus.CREATED)
                .header(HttpHeaders.CACHE_CONTROL, "no-store")
                .body(new RegisterResponse(id));
    }
}
