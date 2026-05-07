<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ResQLink — Saving Lives Through Instant Connection</title>
    <meta name="description" content="ResQLink is an AI-powered emergency response platform that instantly connects people in danger to hospitals, ambulances, security responders, fire services, and disaster response teams.">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
    <link rel="stylesheet" href="{{ asset('css/chat.css') }}">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .theme-toggle { background: transparent; border: none; color: var(--grey); cursor: pointer; padding: 8px; border-radius: 50%; transition: all 0.3s; display: flex; align-items: center; justify-content: center; }
        .theme-toggle:hover { background: var(--glass); color: var(--white); }
        :root.light-mode .theme-toggle:hover { background: rgba(0,0,0,0.05); color: var(--black); }
    </style>
    <script src="{{ asset('js/theme.js') }}"></script>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect rx='20' width='100' height='100' fill='%23E50914'/><text y='.88em' x='10' font-size='70' fill='white' font-weight='bold'>R</text></svg>">
</head>
<body>

<!-- ===== NAVIGATION ===== -->
<nav class="nav" id="navbar">
    <div class="container">
        <a href="#" class="nav-logo">
            <img src="{{ asset('images/logo.png') }}" alt="ResQLink" style="height: 60px; width: auto; object-fit: contain;">
        </a>
            <ul class="nav-links">
                <li><a href="#problem">Problem</a></li>
                <li><a href="#solution">Solution</a></li>
                <li><a href="#features">Features</a></li>
                <li><a href="#how-it-works">How It Works</a></li>
                <li><a href="{{ route('login') }}" class="login-link">Login</a></li>
                <li><a href="{{ route('register') }}" class="btn-primary btn-sm">Register</a></li>
                <li>
                    <button id="themeToggle" class="theme-toggle" aria-label="Toggle Dark Mode" style="margin-left: 10px;">
                        <i data-lucide="sun" id="themeIcon"></i>
                    </button>
                </li>
            </ul>
        <button class="hamburger" id="hamburger" aria-label="Menu">
            <span></span><span></span><span></span>
        </button>
    </div>
</nav>

<!-- ===== HERO ===== -->
<section class="hero" id="home">
    <div class="container">
        <div class="hero-main-content">
            <h1>Res<span class="brand-red">Q</span>Link</h1>
            <div class="hero-tagline">
                <span id="typing-text">Saving Lives Through</span>
                <span class="type-cursor">|</span>
            </div>
            <p class="hero-sub">AI-Powered Emergency Response Platform that connects people to help, fast.</p>
            <div class="hero-buttons">
                <a href="{{ route('register') }}" class="btn-primary">
                    <span>Join the Rescue Network</span>
                    <i data-lucide="arrow-right"></i>
                </a>
                <a href="{{ route('register.partner') }}" class="btn-outline">
                    <i data-lucide="shield"></i>
                    <span>Partner Portal</span>
                </a>
            </div>
        </div>

        <div class="hero-features-row">
            <div class="hero-feature-item">
                <i data-lucide="zap"></i>
                <span>Instant Alert</span>
            </div>
            <div class="hero-feature-item">
                <i data-lucide="truck"></i>
                <span>Fast Response</span>
            </div>
            <div class="hero-feature-item">
                <i data-lucide="map-pinned"></i>
                <span>Smart Routing</span>
            </div>
            <div class="hero-feature-item">
                <i data-lucide="wifi"></i>
                <span>Always Connected</span>
            </div>
        </div>
    </div>
</section>

<!-- ===== PROBLEM ===== -->
<section class="problem" id="problem">
    <div class="container">
        <div class="text-center fade-up">
            <div class="section-label" style="justify-content:center">The Problem</div>
            <h2 class="section-title">In Emergencies, Every Second Counts</h2>
            <p class="section-desc center">But help is not always a call away. Current systems are fragmented, slow, and fail when people need them most.</p>
        </div>
        <div class="problem-grid">
            <div class="problem-card fade-up">
                <div class="icon"><i data-lucide="ban" class="lucide-icon"></i></div>
                <h4>No Centralized System</h4>
                <p>No single platform connecting hospitals, police, fire services, and emergency responders together.</p>
            </div>
            <div class="problem-card fade-up">
                <div class="icon"><i data-lucide="clock" class="lucide-icon"></i></div>
                <h4>Slow Response Times</h4>
                <p>Manual processes delay ambulance and security dispatch when every second is critical.</p>
            </div>
            <div class="problem-card fade-up">
                <div class="icon"><i data-lucide="hospital" class="lucide-icon"></i></div>
                <h4>No Real-Time Hospital Data</h4>
                <p>No visibility into which hospitals have available beds, equipment, or emergency capacity.</p>
            </div>
            <div class="problem-card fade-up">
                <div class="icon"><i data-lucide="unlink" class="lucide-icon"></i></div>
                <h4>Poor Coordination</h4>
                <p>Lack of coordination between hospitals, police, fire services, and emergency responders.</p>
            </div>
            <div class="problem-card fade-up">
                <div class="icon"><i data-lucide="wifi-off" class="lucide-icon"></i></div>
                <h4>Limited Offline Access</h4>
                <p>People without internet are completely cut off from getting emergency help.</p>
            </div>
            <div class="problem-card fade-up">
                <div class="icon"><i data-lucide="heart-off" class="lucide-icon"></i></div>
                <h4>High Mortality</h4>
                <p>Delayed response and poor resource visibility leads to preventable deaths every day.</p>
            </div>
        </div>
    </div>
</section>

<!-- ===== SOLUTION ===== -->
<section class="solution" id="solution">
    <div class="container">
        <div class="fade-up">
            <div class="section-label">The Solution</div>
            <h2 class="section-title">One Platform. Every Emergency.<br>Instant Connection.</h2>
            <p class="section-desc">ResQLink is an all-in-one AI-powered emergency response platform that allows users to trigger emergency help through a mobile app, USSD, or SMS.</p>
        </div>
        <div class="solution-content">
            <div class="solution-features fade-up">
                <div class="solution-feature">
                    <div class="feat-icon"><i data-lucide="siren" class="lucide-icon"></i></div>
                    <div><h4>One-Tap SOS Alert</h4><p>Instantly trigger emergency help with a single tap.</p></div>
                </div>
                <div class="solution-feature">
                    <div class="feat-icon"><i data-lucide="brain-circuit" class="lucide-icon"></i></div>
                    <div><h4>AI-Powered Routing</h4><p>Smart algorithms match each emergency to the nearest, best-suited responder.</p></div>
                </div>
                <div class="solution-feature">
                    <div class="feat-icon"><i data-lucide="map-pin" class="lucide-icon"></i></div>
                    <div><h4>GPS-Based Dispatch</h4><p>Automatic location capture for precise ambulance and responder dispatch.</p></div>
                </div>
                <div class="solution-feature">
                    <div class="feat-icon"><i data-lucide="activity" class="lucide-icon"></i></div>
                    <div><h4>Real-Time Hospital Data</h4><p>Live availability of beds, equipment, and emergency capacity.</p></div>
                </div>
                <div class="solution-feature">
                    <div class="feat-icon"><i data-lucide="smartphone" class="lucide-icon"></i></div>
                    <div><h4>Offline Support</h4><p>Works via USSD and SMS for users without internet access.</p></div>
                </div>
                <div class="solution-feature">
                    <div class="feat-icon"><i data-lucide="radar" class="lucide-icon"></i></div>
                    <div><h4>Real-Time Tracking</h4><p>Track responders and ambulances in real time until help arrives.</p></div>
                </div>
            </div>
            <div class="solution-visual fade-up">
                <div class="sos-circle">SOS</div>
            </div>
        </div>
    </div>
</section>

<!-- ===== EMERGENCY CATEGORIES ===== -->
<section class="categories" id="categories">
    <div class="container">
        <div class="text-center fade-up">
            <div class="section-label" style="justify-content:center">Coverage</div>
            <h2 class="section-title">Complete Emergency Coverage</h2>
            <p class="section-desc center">From medical emergencies to natural disasters, ResQLink covers every critical situation.</p>
        </div>
        <div class="cat-grid">
            <div class="cat-card fade-up">
                <div class="cat-icon"><i data-lucide="heart-pulse" class="lucide-icon lg"></i></div>
                <h3>Health Emergencies</h3>
                <ul>
                    <li>Medical emergencies</li>
                    <li>Accidents & trauma</li>
                    <li>Childbirth complications</li>
                    <li>Sick person support</li>
                    <li>Hospital access & routing</li>
                </ul>
            </div>
            <div class="cat-card fade-up">
                <div class="cat-icon"><i data-lucide="shield-alert" class="lucide-icon lg"></i></div>
                <h3>Security Emergencies</h3>
                <ul>
                    <li>Armed robbery</li>
                    <li>Kidnapping / abduction</li>
                    <li>Home invasion</li>
                    <li>Assault / physical attack</li>
                    <li>Suspicious activity</li>
                </ul>
            </div>
            <div class="cat-card fade-up">
                <div class="cat-icon"><i data-lucide="flame" class="lucide-icon lg"></i></div>
                <h3>Fire Emergencies</h3>
                <ul>
                    <li>Building fire</li>
                    <li>Electrical fire</li>
                    <li>Market fire</li>
                    <li>Industrial fire</li>
                </ul>
            </div>
            <div class="cat-card fade-up">
                <div class="cat-icon"><i data-lucide="cloud-lightning" class="lucide-icon lg"></i></div>
                <h3>Disasters & Hazards</h3>
                <ul>
                    <li>Flooding</li>
                    <li>Building collapse</li>
                    <li>Road accidents</li>
                    <li>Community hazards</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- ===== HOW IT WORKS ===== -->
<section class="how-it-works" id="how-it-works">
    <div class="container">
        <div class="text-center fade-up">
            <div class="section-label" style="justify-content:center">Process</div>
            <h2 class="section-title">How ResQLink Works</h2>
            <p class="section-desc center">From alert to resolution in five seamless steps.</p>
        </div>
        <div class="steps fade-up">
            <div class="step">
                <div class="step-num">1</div>
                <h4>Alert</h4>
                <p>User triggers SOS through app, USSD, or SMS</p>
            </div>
            <div class="step">
                <div class="step-num">2</div>
                <h4>Analyze</h4>
                <p>AI detects location, emergency type, and urgency</p>
            </div>
            <div class="step">
                <div class="step-num">3</div>
                <h4>Dispatch</h4>
                <p>System alerts nearest available responders</p>
            </div>
            <div class="step">
                <div class="step-num">4</div>
                <h4>Respond</h4>
                <p>Ambulance, police, fire, or health facility responds</p>
            </div>
            <div class="step">
                <div class="step-num">5</div>
                <h4>Resolve</h4>
                <p>Case tracked until help arrives and incident is closed</p>
            </div>
        </div>
    </div>
</section>

<!-- ===== KEY FEATURES ===== -->
<section class="features" id="features">
    <div class="container">
        <div class="text-center fade-up">
            <div class="section-label" style="justify-content:center">Features</div>
            <h2 class="section-title">Built for Real Emergencies</h2>
            <p class="section-desc center">Every feature is designed to save lives faster.</p>
        </div>
        <div class="feat-grid">
            <div class="feat-card fade-up">
                <div class="f-icon"><i data-lucide="siren" class="lucide-icon"></i></div>
                <h4>One-Tap SOS</h4>
                <p>Instantly send an emergency alert with your precise location in one tap.</p>
            </div>
            <div class="feat-card fade-up">
                <div class="f-icon"><i data-lucide="map-pin" class="lucide-icon"></i></div>
                <h4>Live Tracking</h4>
                <p>Track ambulance or responder movement in real time on the map.</p>
            </div>
            <div class="feat-card fade-up">
                <div class="f-icon"><i data-lucide="hospital" class="lucide-icon"></i></div>
                <h4>Smart Hospital Finder</h4>
                <p>Route patients to hospitals with available beds and emergency capacity.</p>
            </div>
            <div class="feat-card fade-up">
                <div class="f-icon"><i data-lucide="volume-x" class="lucide-icon"></i></div>
                <h4>Security Silent SOS</h4>
                <p>Quietly send your location during robbery, kidnapping, or dangerous situations.</p>
            </div>
            <div class="feat-card fade-up">
                <div class="f-icon"><i data-lucide="bell-ring" class="lucide-icon"></i></div>
                <h4>Multi-Channel Alerts</h4>
                <p>Reach help through App, SMS, USSD, and emergency contact notifications.</p>
            </div>
            <div class="feat-card fade-up">
                <div class="f-icon"><i data-lucide="brain-circuit" class="lucide-icon"></i></div>
                <h4>AI-Powered Routing</h4>
                <p>Match each emergency to the best available responder automatically.</p>
            </div>
            <div class="feat-card fade-up">
                <div class="f-icon"><i data-lucide="clipboard-list" class="lucide-icon"></i></div>
                <h4>Emergency History</h4>
                <p>Keep complete records of past alerts, responses, and resolution status.</p>
            </div>
            <div class="feat-card fade-up">
                <div class="f-icon"><i data-lucide="layout-dashboard" class="lucide-icon"></i></div>
                <h4>Responder Dashboard</h4>
                <p>Help hospitals, ambulances, police, and fire services manage requests efficiently.</p>
            </div>
        </div>
    </div>
</section>



<!-- ===== IMPACT ===== -->
<section class="impact" id="impact">
    <div class="container">
        <div class="text-center fade-up">
            <div class="section-label" style="justify-content:center">Impact</div>
            <h2 class="section-title">Building Safer Communities</h2>
        </div>
        <div class="impact-grid fade-up">
            <div class="impact-item">
                <div class="imp-icon"><i data-lucide="zap" class="lucide-icon"></i></div>
                <p>Faster Emergency Response</p>
            </div>
            <div class="impact-item">
                <div class="imp-icon"><i data-lucide="heart" class="lucide-icon"></i></div>
                <p>Reduced Preventable Deaths</p>
            </div>
            <div class="impact-item">
                <div class="imp-icon"><i data-lucide="hospital" class="lucide-icon"></i></div>
                <p>Better Hospital Access</p>
            </div>
            <div class="impact-item">
                <div class="imp-icon"><i data-lucide="shield-check" class="lucide-icon"></i></div>
                <p>Safer Communities</p>
            </div>
            <div class="impact-item">
                <div class="imp-icon"><i data-lucide="link" class="lucide-icon"></i></div>
                <p>Improved Coordination</p>
            </div>
            <div class="impact-item">
                <div class="imp-icon"><i data-lucide="trending-up" class="lucide-icon"></i></div>
                <p>Smarter Planning</p>
            </div>
            <div class="impact-item">
                <div class="imp-icon"><i data-lucide="globe" class="lucide-icon"></i></div>
                <p>Disaster Preparedness</p>
            </div>
        </div>
    </div>
</section>

<!-- ===== TEAM ===== -->
<section class="team" id="team">
    <div class="container">
        <div class="text-center fade-up">
            <div class="section-label" style="justify-content:center">Team</div>
            <h2 class="section-title">Meet the Team</h2>
            <p class="section-desc center">Passionate founders committed to saving lives through technology.</p>
        </div>
        <div class="team-grid fade-up">
            <div class="team-card">
                <div class="avatar">AK</div>
                <h4>Amamihechukwu K. O.</h4>
                <p class="role">Founder & CEO</p>
            </div>
            <div class="team-card">
                <div class="avatar">EN</div>
                <h4>Emmanuel N.</h4>
                <p class="role">Chief Technology Officer</p>
            </div>
            <div class="team-card">
                <div class="avatar">EO</div>
                <h4>Elizabeth O.</h4>
                <p class="role">COO — Data Analyst Expert</p>
            </div>
        </div>
    </div>
</section>

<!-- ===== FUNDING ===== -->
<section class="funding" id="funding">
    <div class="container">
        <div class="text-center fade-up">
            <div class="section-label" style="justify-content:center">Investment</div>
            <h2 class="section-title">Funding Ask</h2>
            <div class="funding-amount">₦70,000,000 Seed Round</div>
        </div>
        <div class="funding-bars fade-up">
            <div class="fund-item">
                <label>Technology Development <span>40%</span></label>
                <div class="fund-bar"><div class="fund-bar-fill" data-width="40%" style="width:0"></div></div>
            </div>
            <div class="fund-item">
                <label>Operations & Partnerships <span>30%</span></label>
                <div class="fund-bar"><div class="fund-bar-fill" data-width="30%" style="width:0"></div></div>
            </div>
            <div class="fund-item">
                <label>Marketing & Growth <span>20%</span></label>
                <div class="fund-bar"><div class="fund-bar-fill" data-width="20%" style="width:0"></div></div>
            </div>
            <div class="fund-item">
                <label>Team Expansion <span>10%</span></label>
                <div class="fund-bar"><div class="fund-bar-fill" data-width="10%" style="width:0"></div></div>
            </div>
        </div>
    </div>
</section>

<!-- ===== CONTACT ===== -->
<section class="contact" id="contact">
    <div class="container">
        <div class="text-center fade-up">
            <div class="section-label" style="justify-content:center">Contact</div>
            <h2 class="section-title">Let's Build a Safer Future Together</h2>
            <p class="section-desc center">Interested in partnering, investing, or learning more? Reach out.</p>
        </div>
        <div class="contact-wrapper fade-up">
            <div class="contact-info">
                <h3>Get Involved</h3>
                <p>Whether you're an investor, government agency, hospital, or emergency responder — we'd love to hear from you.</p>
                <div class="contact-ctas">
                    <a href="#contact"><i data-lucide="presentation" class="lucide-icon sm"></i> Request Demo</a>
                    <a href="#contact"><i data-lucide="handshake" class="lucide-icon sm"></i> Partner With Us</a>
                    <a href="#contact"><i data-lucide="briefcase" class="lucide-icon sm"></i> Invest in ResQLink</a>
                </div>
            </div>
            <form class="contact-form" id="contact-form">
                <div class="form-row">
                    <input type="text" placeholder="Your Name" required id="contact-name">
                    <input type="email" placeholder="Email Address" required id="contact-email">
                </div>
                <div class="form-row">
                    <input type="tel" placeholder="Phone Number" id="contact-phone">
                    <input type="text" placeholder="Organization" id="contact-org">
                </div>
                <textarea placeholder="Your Message" required id="contact-message"></textarea>
                <button type="submit" class="btn-primary" style="width:100%">Send Message</button>
            </form>
        </div>
    </div>
</section>

<!-- ===== FOOTER ===== -->
<footer class="footer">
    <div class="container">
        <div class="footer-top">
            <div class="footer-brand">
                <img src="{{ asset('images/logo.png') }}" alt="ResQLink" style="height: 60px; width: auto; object-fit: contain; margin-bottom: 15px;">
                <p class="footer-tagline">Saving Lives Through Instant Connection</p>
            </div>
            <div class="footer-col">
                <h5>Platform</h5>
                <a href="#solution">Solution</a>
                <a href="#features">Features</a>
                <a href="#how-it-works">How It Works</a>
                <a href="#categories">Coverage</a>
            </div>
            <div class="footer-col">
                <h5>Company</h5>
                <a href="#team">Team</a>
                <a href="#market">Business Model</a>
                <a href="#funding">Funding</a>
                <a href="#impact">Impact</a>
            </div>
            <div class="footer-col">
                <h5>Connect</h5>
                <a href="#contact">Contact Us</a>
                <a href="#contact">Request Demo</a>
                <a href="#contact">Partner With Us</a>
                <a href="#contact">Invest</a>
            </div>
        </div>
        <div class="footer-bottom">
            &copy; {{ date('Y') }} ResQLink. All rights reserved. Saving lives through instant connection.
        </div>
    </div>
</footer>

<script src="{{ asset('js/landing.js') }}"></script>
<script src="{{ asset('js/chat.js') }}"></script>
<script>lucide.createIcons();</script>
</body>
</html>
