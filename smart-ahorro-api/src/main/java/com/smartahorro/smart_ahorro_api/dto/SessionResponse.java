package com.smartahorro.smart_ahorro_api.dto;
public record SessionResponse(String accessToken, String tokenType, long expiresIn,
                              String refreshToken, long refreshExpiresIn) {}
