<?php

$password_verification_mail_body = '<table align="center" border="0" cellpadding="0" cellspacing="0" height="100%"
        style="display: block;margin:0 auto;width:100%;max-width:670px;border-top:7px solid #2bc155;font-family:Helvetica,arial;background:#fafafa">
        <tbody>
            <tr>
                <td align="center" valign="top">
                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                        <tbody>
                            <tr>
                                <td valign="top">
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%"
                                        style="min-width:100%">
                                        <tbody>
                                            <tr>
                                                <td valign="top" style="padding:9px">
                                                    <table align="left" width="100%" border="0" cellpadding="0"
                                                        cellspacing="0" style="min-width:100%">
                                                        <tbody>
                                                            <tr>
                                                                <td valign="top"
                                                                    style="padding-right:9px;padding-left:9px;padding-top:50px;padding-bottom:30px;text-align:center">
                                                                    <img align="center" alt=""
                                                                        src="https://app.logzz.com.br/images/logo-full.png"
                                                                        style="max-width:100%;padding-bottom:0;display:inline;vertical-align:bottom"
                                                                        class="CToWUd">
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>

                            <tr>
                                <td valign="top">
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                        <tbody>
                                            <tr>
                                                <td valign="top">

                                                    <table align="left" border="0" cellpadding="0" cellspacing="0"
                                                        width="100%">

                                                        <tbody>

                                                            <tr>

                                                                <td valign="top"
                                                                    style="padding-top:9px;padding-right:18px;padding-bottom:35px;padding-left:18px">

                                                                    <h1
                                                                        style="text-align:center;margin:0;font-size:28px;padding:0 60px">
                                                                        <span style="color:#51585f">Redefinição de senha</span>
                                                                    </h1>

                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>

                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td valign="top">
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%"
                                        style="min-width:100%!important">

                                        <tbody>
                                            <tr>
                                                <td valign="top">

                                                    <table align="left" border="0" cellpadding="0" cellspacing="0"
                                                        width="100%" style="min-width:100%;max-width:100%">
                                                        <tbody>
                                                            <tr>

                                                                <td
                                                                    style="padding-top:9px;padding-left:25px;padding-bottom:9px;padding-right:25px">

                                                                    <table border="0" cellpadding="18" cellspacing="0"
                                                                        width="100%"
                                                                        style="min-width:100%!important;border:2px solid #edeced;background-color:#ffffff;border-radius:4px">
                                                                        <tbody>
                                                                            <tr>
                                                                                <td valign="top"
                                                                                    style="color:#747c83;font-size:15px;font-weight:normal;line-height:150%">
                                                                                    <div style="padding:35px 60px">
                                                                                        <strong>
                                                                                            <span
                                                                                                style="color:#747c83">Olá, ' . @$user_name . '.</span>
                                                                                        </strong>
                                                                                        <br>
                                                                                        <br>Foi solicitado a alteração de senha, utilize o código abaixo para validar a alteração!
                                                                                        <br><br>
                                                                                        Este é o código de validação para realizar a alteração de sua senha:
                                                                                        <br><br>
                                                                                        <strong>Código: </strong> ' . @$validation_code . '<br>
                                                                                        <br>
                                                                                        <br>
                                                                                        <div
                                                                                            style="font-size:0.9em;font-style:italic;color:#b4b7b9;padding-top:10px;margin-top:30px;border-top:1px solid #efefef">
                                                                                            Muito
                                                                                            obrigado, Equipe <span
                                                                                                class="il">Logzz</span>
                                                                                        </div>

                                                                                    </div>
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                        <tbody>
                                            <tr>
                                                <td valign="top" style="padding-bottom:9px">

                                                    <table align="left" border="0" cellpadding="0" cellspacing="0"
                                                        width="65%">
                                                        <tbody>
                                                            <tr>

                                                                <td valign="top"
                                                                    style="padding:9px 0px 9px 25px;color:#617279">

                                                                    <div
                                                                        style="text-align:left;padding-top:8px;padding-bottom:8px;font-size:12px;color:#617279">
                                                                        
                                                                    </div>

                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>

                                                    <table align="right" border="0" cellpadding="0" cellspacing="0"
                                                        width="35%">
                                                        <tbody>
                                                            <tr>

                                                                <td valign="top"
                                                                    style="padding:9px 25px 9px 0px;color:#617279">

                                                                    <div style="text-align:right"><a
                                                                            href="http://msg.dropexpress.com.br/ls/click?upn=BBVr-2BrY3CNwck3wRuhF-2FTUzgrIK2jlk5e83Y06miS3I-3DjqKI_IS6bcIjcPvz9dZjTq3LqkIUhJSFlEZUR9yOLyIcyasA1JeEYG5Cqdn0Tz95hNleOmWcCxnSvzcn-2FcmB9p04NCk4sHgV-2BI34Rnw9lXuiTiE7r8aKTCOSrUJhr8-2FSxyseO8e-2FtOV-2FgUvoMuw1qJt-2BO5ysIfb-2FGFEyH0DKcX-2FZpZNqXBe-2BylF-2Bjb5BCP7wsri-2F6y49dOKU1SU0oCQ3uEJlLHoKCJE0fn5kMS9Cufp6X3mFNZDlnqaPyf3V8qypwHhC3lC0-2ByblhjKpjxvXo-2BaRg7j8bPVq7-2BSnUdT4EBi-2Bpgk-2BYU6EHXFqvYYsxrZK1obvD-2BDndYLUrzQ2iYc4UmzUmMigyw2ycIOqkhNgafm0Avic-3D"
                                                                            target="_blank"
                                                                            data-saferedirecturl="https://www.google.com/url?q=http://msg.dropexpress.com.br/ls/click?upn%3DBBVr-2BrY3CNwck3wRuhF-2FTUzgrIK2jlk5e83Y06miS3I-3DjqKI_IS6bcIjcPvz9dZjTq3LqkIUhJSFlEZUR9yOLyIcyasA1JeEYG5Cqdn0Tz95hNleOmWcCxnSvzcn-2FcmB9p04NCk4sHgV-2BI34Rnw9lXuiTiE7r8aKTCOSrUJhr8-2FSxyseO8e-2FtOV-2FgUvoMuw1qJt-2BO5ysIfb-2FGFEyH0DKcX-2FZpZNqXBe-2BylF-2Bjb5BCP7wsri-2F6y49dOKU1SU0oCQ3uEJlLHoKCJE0fn5kMS9Cufp6X3mFNZDlnqaPyf3V8qypwHhC3lC0-2ByblhjKpjxvXo-2BaRg7j8bPVq7-2BSnUdT4EBi-2Bpgk-2BYU6EHXFqvYYsxrZK1obvD-2BDndYLUrzQ2iYc4UmzUmMigyw2ycIOqkhNgafm0Avic-3D&amp;source=gmail&amp;ust=1637873316373000&amp;usg=AOvVaw0jV2cPoUYcSDH7q53n_fxj"><em><img
                                                                                    align="none" height="24"
                                                                                    src="https://app.logzz.com.br/images/logo-full.png"
                                                                                    style="width:162px;height:29px;margin:0px"
                                                                                    width="162" class="CToWUd"></em></a>
                                                                    </div>

                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>

                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>


                                </td>
                            </tr>
                        </tbody>
                    </table>

                </td>
            </tr>
        </tbody>
    </table>';
    
    ?>