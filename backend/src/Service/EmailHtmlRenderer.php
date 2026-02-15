<?php

declare(strict_types=1);

namespace App\Service;

/**
 * Génère le HTML des e-mails LudoPlanet (en-tête avec logo, contenu, pied de page).
 */
final class EmailHtmlRenderer
{
    private const BRAND_ACCENT = '#c45318';
    private const BRAND_DARK = '#303047';

    public function __construct(
        private readonly string $frontendUrl
    ) {
    }

    /**
     * @param array{title: string, body: string, ctaUrl?: string, ctaLabel?: string} $params
     */
    public function render(array $params): string
    {
        $baseUrl = rtrim($this->frontendUrl, '/');
        $logoUrl = $baseUrl . '/logo.png';
        // Pour afficher le logo dans les e-mails : copiez frontend/src/lib/assets/icons/logoLudo.png vers frontend/static/logo.png

        $title = htmlspecialchars($params['title'], \ENT_QUOTES, 'UTF-8');
        $body = $params['body'];
        $ctaHtml = '';
        if (!empty($params['ctaUrl']) && !empty($params['ctaLabel'])) {
            $ctaUrl = htmlspecialchars($params['ctaUrl'], \ENT_QUOTES, 'UTF-8');
            $ctaLabel = htmlspecialchars($params['ctaLabel'], \ENT_QUOTES, 'UTF-8');
            $ctaHtml = sprintf(
                '<p style="margin: 24px 0 0;"><a href="%s" style="display: inline-block; padding: 12px 24px; background: %s; color: #fff; text-decoration: none; font-weight: bold; border-radius: 8px;">%s</a></p>',
                $ctaUrl,
                self::BRAND_ACCENT,
                $ctaLabel
            );
        }

        $headerContent = '<img src="' . $logoUrl . '" alt="LudoPlanet" width="120" height="auto" style="display: inline-block; max-height: 80px;" />';

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{$title}</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5;">
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background: #f5f5f5;">
    <tr>
      <td align="center" style="padding: 32px 16px;">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width: 560px; background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
          <tr>
            <td style="padding: 32px 32px 24px; text-align: center; background: #303047; border-radius: 12px 12px 0 0;">
              {$headerContent}
            </td>
          </tr>
          <tr>
            <td style="padding: 28px 32px 32px;">
              <h1 style="margin: 0 0 16px; font-size: 20px; color: #303047; font-weight: 700;">{$title}</h1>
              <div style="color: #374151; font-size: 15px; line-height: 1.6;">
                {$body}
              </div>
              {$ctaHtml}
            </td>
          </tr>
          <tr>
            <td style="padding: 20px 32px; text-align: center; font-size: 12px; color: #9ca3af; border-top: 1px solid #e5e7eb;">
              LudoPlanet — Votre boutique de jeux de société
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;
    }

}
