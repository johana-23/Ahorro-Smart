package com.smartahorro.smart_ahorro_api.dto;

public record LoginResponse(
        String accessToken,
        String tokenType,
        long expiresIn
) {
}