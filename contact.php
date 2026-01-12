<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us | KIDV Tech</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/png" href="android-icon-36x36.png">
    <style>
      /* Small inline styles for status messages; keep in style.css if you prefer */
      .form-status { margin-top: 12px; font-weight: 600; }
      .form-status.success { color: #157f2e; }
      .form-status.error { color: #b21f2d; }
      .btn-quote[disabled] { opacity: 0.6; cursor: not-allowed; }
    </style>
</head>
<body>

<header>
    <div class="header-container">
        <div class="logo"><img src="logo.png" alt="KIDV Tech"></div>
        <nav>
            <a href="index.html">Home</a>
            <a href="company.html">Company</a>
            <a href="contact.html">Contact Us</a>
        </nav>
    </div>
</header>

<section class="contact-section" id="contact">
    <div class="container">
        <p class="sub-heading">GET IN TOUCH</p>
        <h2 class="main-heading">Ready to Grow Your Business?</h2>
        
        <div class="contact-wrapper">
            <div class="contact-info">
                <div class="info-card">
                    <i class="fas fa-map-marker-alt"></i>
                    <div>
                        <h4>Our Location</h4>
                        <p>Ahmedabad, Gujarat, India</p>
                    </div>
                </div>
                <div class="info-card">
                    <i class="fas fa-envelope"></i>
                    <div>
                        <h4>Email Us</h4>
                        <p><a href="mailto:amit@kidvtech.com">amit@kidvtech.com</a></p>
                    </div>
                </div>
                <div class="info-card">
                    <i class="fas fa-phone-alt"></i>
                    <div>
                        <h4>Call Us</h4>
                        <p><a href="tel:+917984486885">+91 79844 86885</a></p>
                    </div>
                </div>
            </div>

            <div class="contact-form-box">
                <!-- Updated: form posts to process_contact.php (server-side PHP) -->
                <form id="kidvContactForm" action="process_contact.php" method="post" novalidate>
                    <div class="form-group">
                        <input type="text" name="name" placeholder="Your Name" required maxlength="100">
                    </div>
                    <div class="form-group">
                        <input type="email" name="email" placeholder="Your Email" required maxlength="150">
                    </div>
                    <div class="form-group">
                        <select name="solution" required>
                            <option value="" disabled selected>Select Solution</option>
                            <option value="Website Development">Website Development</option>
                            <option value="Mobile Apps Development">Mobile Apps Development</option>
                            <option value="ERPNext Solutions">ERPNext Solutions</option>
                            <option value="AI Solutions">AI Solutions</option>
                            <option value="HR Software Solutions">HR Software Solutions</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <textarea name="message" placeholder="Tell us about your project..." rows="5" required maxlength="2000"></textarea>
                    </div>

                    <!-- Honeypot (optional simple spam protection) -->
                    <div style="display:none;">
                      <label>Leave this field empty: <input type="text" name="hp_field" tabindex="-1" autocomplete="off"></label>
                    </div>

                    <button type="submit" class="btn-quote">Send Message</button>

                    <!-- Status area -->
                    <div id="formStatus" class="form-status" aria-live="polite"></div>
                </form>

                <!-- Noscript fallback -->
                <noscript>
                  <p style="color:#b21f2d;">JavaScript is disabled in your browser. The form will still submit but you won't see inline confirmation. Ensure process_contact.php exists on the server.</p>
                </noscript>
            </div>
        </div>
    </div>
</section>

<footer class="main-footer">
    <div class="container">
        <div class="footer-content">
            <p>&copy; 2026 <b>KIDV Tech</b>. All Rights Reserved.</p>
            <div class="footer-links">
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
            </div>
        </div>
    </div>
</footer>

<script>
/*
  AJAX submit:
  - Submits to process_contact.php (expects JSON { success: bool, message: string })
  - Disables the button while sending and shows success/error feedback
  - Fallback: if JS fails, form will POST normally to process_contact.php
*/
(function () {
  const form = document.getElementById('kidvContactForm');
  const statusEl = document.getElementById('formStatus');
  const submitBtn = form.querySelector('button[type="submit"]');

  form.addEventListener('submit', async function (e) {
    e.preventDefault();
    statusEl.textContent = '';
    statusEl.className = 'form-status';
    // simple honeypot check client-side (server must also check hp_field)
    if (form.hp_field && form.hp_field.value.trim() !== '') {
      // likely bot, silently stop
      statusEl.textContent = 'Spam detected.';
      statusEl.classList.add('error');
      return;
    }

    // Basic client validation (browser will handle required, but double-check)
    const formData = new FormData(form);
    // disable submit
    submitBtn.disabled = true;
    submitBtn.setAttribute('aria-busy', 'true');
    submitBtn.textContent = 'Sending...';

    try {
      const resp = await fetch(form.action, {
        method: 'POST',
        body: formData,
        credentials: 'same-origin',
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      // If server returns non-JSON (e.g., PHP error), handle gracefully
      const contentType = resp.headers.get('Content-Type') || '';
      let data;
      if (contentType.indexOf('application/json') !== -1) {
        data = await resp.json();
      } else {
        // fallback - try text
        const text = await resp.text();
        try { data = JSON.parse(text); } catch (err) { data = { success: resp.ok, message: text || (resp.ok ? 'Submitted' : 'Server error') }; }
      }

      if (data && data.success) {
        statusEl.classList.add('success');
        statusEl.textContent = data.message || 'Thanks — your enquiry has been submitted.';
        form.reset();
      } else {
        statusEl.classList.add('error');
        statusEl.textContent = data && data.message ? data.message : 'Could not send. Please try again later.';
      }
    } catch (err) {
      statusEl.classList.add('error');
      statusEl.textContent = 'Network error. Please check your connection and try again.';
      console.error(err);
    } finally {
      submitBtn.disabled = false;
      submitBtn.removeAttribute('aria-busy');
      submitBtn.textContent = 'Send Message';
    }
  });
})();
</script>

</body>
</html>
