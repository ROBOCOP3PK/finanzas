<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contrasena</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f3f4f6;">
    <table role="presentation" style="width: 100%; border-collapse: collapse;">
        <tr>
            <td align="center" style="padding: 40px 0;">
                <table role="presentation" style="width: 100%; max-width: 600px; border-collapse: collapse; background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="padding: 40px 40px 20px; text-align: center;">
                            <h1 style="margin: 0; font-size: 28px; font-weight: bold; color: #4f46e5;">
                                Finanzas
                            </h1>
                            <p style="margin: 10px 0 0; font-size: 14px; color: #6b7280;">
                                Control de gastos compartidos
                            </p>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 20px 40px;">
                            <h2 style="margin: 0 0 20px; font-size: 20px; color: #111827; text-align: center;">
                                Recuperar contrasena
                            </h2>
                            <p style="margin: 0 0 30px; font-size: 16px; color: #4b5563; text-align: center; line-height: 1.6;">
                                Usa el siguiente codigo para restablecer tu contrasena:
                            </p>

                            <!-- Code Box -->
                            <div style="background-color: #f3f4f6; border-radius: 8px; padding: 20px; text-align: center; margin-bottom: 30px;">
                                <span style="font-size: 36px; font-weight: bold; letter-spacing: 8px; color: #4f46e5; font-family: monospace;">
                                    {{ $code }}
                                </span>
                            </div>

                            <p style="margin: 0 0 10px; font-size: 14px; color: #6b7280; text-align: center;">
                                Este codigo expira en <strong>{{ $expiresInMinutes }} minutos</strong>.
                            </p>
                            <p style="margin: 0; font-size: 14px; color: #9ca3af; text-align: center;">
                                Si no solicitaste este codigo, puedes ignorar este mensaje.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding: 30px 40px; border-top: 1px solid #e5e7eb;">
                            <p style="margin: 0; font-size: 12px; color: #9ca3af; text-align: center;">
                                Este es un correo automatico, por favor no respondas.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
