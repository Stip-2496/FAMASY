<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - FAMASY</title>
    <style>
        /* Media queries para responsive */
        @media only screen and (max-width: 600px) {
            .email-container {
                width: 95% !important;
                max-width: 95% !important;
            }
            .content-padding {
                padding: 30px 20px !important;
            }
            .header-padding {
                padding: 30px 20px !important;
            }
            .footer-padding {
                padding: 20px 15px !important;
            }
            .button-text {
                font-size: 16px !important;
                padding: 15px 30px !important;
            }
        }
        
        @media only screen and (min-width: 601px) and (max-width: 900px) {
            .email-container {
                width: 90% !important;
                max-width: 700px !important;
            }
        }
        
        @media only screen and (min-width: 901px) {
            .email-container {
                width: 800px !important;
                max-width: 800px !important;
            }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; background-color: #f4f7fa;">
    
    <!-- Container principal -->
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f7fa; padding: 20px 0;">
        <tr>
            <td align="center">
                
                <!-- Email content wrapper con clase responsive -->
                <table cellpadding="0" cellspacing="0" class="email-container" style="width: 800px; max-width: 800px; background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0, 120, 50, 0.1); overflow: hidden;">
                    
                    <!-- Header con gradiente -->
                    <tr>
                        <td class="header-padding" style="background: linear-gradient(135deg, #007832 0%, #28a745 100%); padding: 40px 30px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 32px; font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                                FAMASY Technologies
                            </h1>
                            <p style="color: rgba(255,255,255,0.9); margin: 8px 0 0 0; font-size: 16px; font-weight: 300;">
                                Sistema de Gestión Integral
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Contenido principal -->
                    <tr>
                        <td class="content-padding" style="padding: 50px 40px;">
                            
                            <!-- Icono y título -->
                            <div style="text-align: center; margin-bottom: 30px;">
                                <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #007832, #28a745); border-radius: 50%; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 25px rgba(0, 120, 50, 0.3);">
                                    <span style="color: white; font-size: 36px; font-weight: bold;">🔐</span>
                                </div>
                                <h2 style="color: #2c3e50; margin: 0; font-size: 28px; font-weight: 600;">
                                    Restablecer Contraseña
                                </h2>
                                <p style="color: #7f8c8d; margin: 10px 0 0 0; font-size: 16px;">
                                    Solicitud de cambio de contraseña
                                </p>
                            </div>
                            
                            <!-- Mensaje principal -->
                            <div style="background: #f8f9fa; padding: 30px; border-radius: 10px; margin-bottom: 30px; border-left: 4px solid #007832;">
                                <p style="color: #34495e; font-size: 17px; line-height: 1.7; margin: 0 0 20px 0;">
                                    <strong>¡Hola!</strong>
                                </p>
                                <p style="color: #34495e; font-size: 17px; line-height: 1.7; margin: 0;">
                                    Has solicitado restablecer la contraseña de tu cuenta en <strong style="color: #007832;">FAMASY Technologies</strong>. 
                                    Para crear una nueva contraseña, haz clic en el botón de abajo:
                                </p>
                            </div>
                            
                            <!-- Botón principal -->
                            <div style="text-align: center; margin: 40px 0;">
                                <a href="{{ $actionUrl }}" class="button-text" style="display: inline-block; background: linear-gradient(135deg, #007832 0%, #28a745 100%); color: #ffffff; text-decoration: none; padding: 18px 40px; border-radius: 50px; font-size: 18px; font-weight: 600; box-shadow: 0 6px 20px rgba(0, 120, 50, 0.4);">
                                    ✨ Crear Nueva Contraseña
                                </a>
                            </div>
                            
                            <!-- Información importante -->
                            <div style="background: #e8f5e8; border: 1px solid #b8e6b8; border-radius: 8px; padding: 20px; margin: 30px 0;">
                                <p style="color: #2d5a2d; margin: 0 0 15px 0; font-size: 16px; font-weight: 600;">
                                    ⏰ Información importante:
                                </p>
                                <ul style="color: #2d5a2d; margin: 0; padding-left: 20px; font-size: 15px; line-height: 1.6;">
                                    <li>Este enlace expirará en <strong>{{ config('auth.passwords.'.config('auth.defaults.passwords').'.expire') }} minutos</strong> por seguridad</li>
                                    <li>Solo puedes usar este enlace una vez</li>
                                    <li>Si no solicitaste este cambio, puedes ignorar este correo</li>
                                </ul>
                            </div>
                            
                            <!-- Alerta de seguridad -->
                            <div style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px; padding: 20px; margin: 30px 0;">
                                <p style="color: #8b7300; margin: 0; font-size: 15px; line-height: 1.6;">
                                    🛡️ <strong>Seguridad:</strong> Si no solicitaste restablecer tu contraseña, tu cuenta sigue siendo segura. 
                                    Puedes ignorar este correo y tu contraseña actual seguirá funcionando normalmente.
                                </p>
                            </div>
                            
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td class="footer-padding" style="background: #2c3e50; padding: 30px 40px; text-align: center;">
                            <h3 style="color: #ffffff; margin: 0 0 10px 0; font-size: 20px; font-weight: 600;">
                                FAMASY Technologies
                            </h3>
                            <p style="color: #bdc3c7; margin: 0 0 15px 0; font-size: 14px;">
                                Sistema de Gestión Integral para el Agro
                            </p>
                            <p style="color: #95a5a6; margin: 0; font-size: 12px;">
                                Este es un correo automático, por favor no responder directamente
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Enlace alternativo -->
                    <tr>
                        <td style="background: #ecf0f1; padding: 20px 40px; text-align: center; font-size: 12px; color: #7f8c8d; border-radius: 0 0 12px 12px;">
                            <p style="margin: 0 0 10px 0; font-weight: 600;">¿Problemas con el botón?</p>
                            <p style="margin: 0; word-break: break-all; line-height: 1.4;">
                                Copia y pega este enlace en tu navegador:<br>
                                <a href="{{ $actionUrl }}" style="color: #007832; text-decoration: none;">{{ $actionUrl }}</a>
                            </p>
                        </td>
                    </tr>
                    
                </table>
                
            </td>
        </tr>
    </table>
    
</body>
</html>