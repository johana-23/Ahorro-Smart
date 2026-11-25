package com.smartahorro.smart_ahorro_api.service;

import java.net.URLEncoder;
import java.nio.charset.StandardCharsets;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.beans.factory.ObjectProvider;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.mail.SimpleMailMessage;
import org.springframework.mail.javamail.JavaMailSender;
import org.springframework.stereotype.Component;

/**
 * Envia el enlace de recuperacion. Nunca propaga errores de envio, para que la
 * respuesta del endpoint no revele si un correo existe.
 */
@Component
public class PasswordResetMailer {

    private static final Logger logger = LoggerFactory.getLogger(PasswordResetMailer.class);

    private final ObjectProvider<JavaMailSender> mailSender;
    private final String baseUrl;
    private final String from;
    private final boolean logLink;

    public PasswordResetMailer(
            ObjectProvider<JavaMailSender> mailSender,
            @Value("${smart-ahorro.password-reset.base-url}") String baseUrl,
            @Value("${smart-ahorro.mail.from}") String from,
            @Value("${smart-ahorro.password-reset.log-link:false}") boolean logLink
    ) {
        this.mailSender = mailSender;
        this.baseUrl = baseUrl.endsWith("/") ? baseUrl.substring(0, baseUrl.length() - 1) : baseUrl;
        this.from = from;
        this.logLink = logLink;
    }

    public void sendResetLink(String email, String token) {
        String link = baseUrl + "/reset-password/" + token
                + "?email=" + URLEncoder.encode(email, StandardCharsets.UTF_8);

        JavaMailSender sender = mailSender.getIfAvailable();
        if (sender == null) {
            if (logLink) {
                logger.warn("SMTP no configurado (modo desarrollo). Enlace de recuperacion: {}", link);
            } else {
                logger.warn("SMTP no configurado: no se envio el enlace de recuperacion.");
            }
            return;
        }

        try {
            SimpleMailMessage message = new SimpleMailMessage();
            message.setFrom(from);
            message.setTo(email);
            message.setSubject("Smart Ahorro - Recuperacion de contrasena");
            message.setText("Recibimos una solicitud para restablecer tu contrasena.\n\n"
                    + "Usa este enlace (vence en 60 minutos y solo funciona una vez):\n"
                    + link + "\n\n"
                    + "Si no fuiste tu, ignora este mensaje.");
            sender.send(message);
        } catch (RuntimeException e) {
            logger.error("No fue posible enviar el correo de recuperacion", e);
        }
    }
}
