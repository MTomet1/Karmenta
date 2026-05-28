<?php
require __DIR__ . '/security/headers.php';
require __DIR__ . '/security/session_boot.php';
require __DIR__ . '/security/csrf.php';
require __DIR__ . '/api_helpers.php';
$weather = get_zagreb_weather(); // vanjski API #2 (Open-Meteo), null ako nedostupan
?>

<div class="contact-shell">

    <div class="section-header" style="margin-bottom:32px;display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap;">
        <div style="display:flex;gap:12px;align-items:flex-start;">
            <div class="section-accent"></div>
            <div>
                <div class="section-label">Javite nam se</div>
                <div class="section-title">Kontakt</div>
            </div>
        </div>
        <?php if ($weather): ?>
        <div title="Trenutno vrijeme u Zagrebu (Open-Meteo API)"
             style="display:flex;align-items:center;gap:10px;background:rgba(255,255,255,0.06);border:1px solid var(--text-muted,#888);border-radius:12px;padding:10px 16px;">
            <span style="font-size:1.6rem;line-height:1;"><?= $weather['icon'] ?></span>
            <div style="line-height:1.25;">
                <div style="font-size:1.1rem;font-weight:600;"><?= $weather['temp'] ?>°C</div>
                <div style="font-size:0.75rem;color:var(--text-muted,#888);">
                    Zagreb · <?= htmlspecialchars($weather['desc']) ?> · <?= $weather['wind'] ?> km/h
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="glass-contact-wrapper">
        <div class="glass-card">

            <div class="glass-map">
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d5089.816683877843!2d16.074784475821673!3d45.82702150915811!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x4766783cfed1611d%3A0x546b76d32db7e8ef!2sDubrava%20258%2C%2010000%2C%20Zagreb!5e1!3m2!1hr!2hr!4v1749204130670!5m2!1hr!2hr"
                    allowfullscreen
                    loading="lazy"></iframe>
            </div>

            <form id="contact_form" class="glass-form" action="send-contact.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                    <div class="glass-field">
                        <input type="text" id="fname" name="firstname" placeholder=" " required>
                        <label for="fname">Ime *</label>
                    </div>
                    <div class="glass-field">
                        <input type="text" id="lname" name="lastname" placeholder=" " required>
                        <label for="lname">Prezime *</label>
                    </div>
                </div>

                <div class="glass-field">
                    <input type="email" id="email" name="email" placeholder=" " required>
                    <label for="email">Email *</label>
                </div>

                <div class="glass-field">
                    <select id="country" name="country">
                        <option value="" disabled selected></option>
                        <option value="BE">Belgium</option>
                        <option value="HR">Croatia</option>
                        <option value="LU">Luxembourg</option>
                        <option value="HU">Hungary</option>
                        <option value="SI">Slovenia</option>
                        <option value="AT">Austria</option>
                        <option value="DE">Germany</option>
                    </select>
                    <label for="country">Država</label>
                </div>

                <div class="glass-field textarea">
                    <textarea id="subject" name="subject" placeholder=" " required></textarea>
                    <label for="subject">Vaša poruka…</label>
                </div>

                <button type="submit" class="glass-btn">Pošalji poruku →</button>
            </form>

        </div>
    </div>

</div>
