<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ResQLink — Saving Lives Through Instant Connection</title>
    <meta name="description" content="ResQLink is an AI-powered emergency response platform that instantly connects people in danger to hospitals, ambulances, security responders, fire services, and disaster response teams.">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/landing.css">
    <link rel="stylesheet" href="/css/chat.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .theme-toggle { background: transparent; border: none; color: var(--grey); cursor: pointer; padding: 8px; border-radius: 50%; transition: all 0.3s; display: flex; align-items: center; justify-content: center; }
        .theme-toggle:hover { background: var(--glass); color: var(--white); }
        :root.light-mode .theme-toggle:hover { background: rgba(0,0,0,0.05); color: var(--black); }
    </style>
    <script src="/js/theme.js"></script>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect rx='20' width='100' height='100' fill='%23E50914'/><text y='.88em' x='10' font-size='70' fill='white' font-weight='bold'>R</text></svg>">
</head>
<body>

<!-- ===== NAVIGATION ===== -->
<nav class="nav" id="navbar">
    <div class="container">
        <a href="#" class="nav-logo">
            <img src="{{ asset('images/logo.png') }}" alt="ResQLink" style="height: 60px; width: auto; object-fit: contain;">
        </a>
            <ul class="nav-links" id="navLinks">
                <li><a href="#home" class="nav-link-item">Home</a></li>

                <li class="nav-divider-line"></li>

                <li class="nav-dropdown">
                    <a href="#categories" class="nav-dropdown-trigger nav-link-item nav-emergency-link">
                        <i data-lucide="heart-pulse" class="nav-em-icon"></i>
                        Medical
                        <i data-lucide="chevron-down" class="dropdown-arrow"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="{{ route('register') }}"><i data-lucide="baby" class="drop-icon"></i>Labor &amp; Delivery Emergency</a></li>
                        <li><a href="{{ route('register') }}"><i data-lucide="heart" class="drop-icon"></i>Pregnancy Complications</a></li>
                        <li><a href="{{ route('register') }}"><i data-lucide="droplets" class="drop-icon"></i>Excessive Bleeding</a></li>
                        <li><a href="{{ route('register') }}"><i data-lucide="users" class="drop-icon"></i>Child Care</a></li>
                        <li><a href="{{ route('register') }}"><i data-lucide="heart-crack" class="drop-icon"></i>Heart Attack</a></li>
                        <li><a href="{{ route('register') }}"><i data-lucide="brain" class="drop-icon"></i>Stroke</a></li>
                        <li><a href="{{ route('register') }}"><i data-lucide="zap" class="drop-icon"></i>Seizure</a></li>
                        <li><a href="{{ route('register') }}"><i data-lucide="user-x" class="drop-icon"></i>Unconscious Person</a></li>
                        <li><a href="{{ route('register') }}"><i data-lucide="wind" class="drop-icon"></i>Severe Breathing Difficulty</a></li>
                    </ul>
                </li>
                <li class="nav-dropdown">
                    <a href="#accident" class="nav-dropdown-trigger nav-link-item nav-emergency-link">
                        <i data-lucide="car-front" class="nav-em-icon"></i>
                        Accident
                        <i data-lucide="chevron-down" class="dropdown-arrow"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="{{ route('register') }}"><i data-lucide="truck" class="drop-icon"></i>Road Traffic Accident</a></li>
                        <li><a href="{{ route('register') }}"><i data-lucide="person-standing" class="drop-icon"></i>Fall Injury</a></li>
                        <li><a href="{{ route('register') }}"><i data-lucide="droplets" class="drop-icon"></i>Severe Bleeding</a></li>
                        <li><a href="{{ route('register') }}"><i data-lucide="bone" class="drop-icon"></i>Fracture/Broken Bone</a></li>
                        <li><a href="{{ route('register') }}"><i data-lucide="brain" class="drop-icon"></i>Head Injury</a></li>
                    </ul>
                </li>
                <li class="nav-dropdown">
                    <a href="#categories" class="nav-dropdown-trigger nav-link-item nav-emergency-link">
                        <i data-lucide="flame" class="nav-em-icon"></i>
                        Fire
                        <i data-lucide="chevron-down" class="dropdown-arrow"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="{{ route('register') }}"><i data-lucide="home" class="drop-icon"></i>Residential Fire</a></li>
                        <li><a href="{{ route('register') }}"><i data-lucide="building-2" class="drop-icon"></i>Commercial Building Fire</a></li>
                        <li><a href="{{ route('register') }}"><i data-lucide="store" class="drop-icon"></i>Market Fire</a></li>
                        <li><a href="{{ route('register') }}"><i data-lucide="zap" class="drop-icon"></i>Electrical Fire</a></li>
                        <li><a href="{{ route('register') }}"><i data-lucide="car" class="drop-icon"></i>Vehicle Fire</a></li>
                        <li><a href="{{ route('register') }}"><i data-lucide="circle-alert" class="drop-icon"></i>Gas Explosion</a></li>
                        <li><a href="{{ route('register') }}"><i data-lucide="factory" class="drop-icon"></i>Industrial Fire</a></li>
                        <li><a href="{{ route('register') }}"><i data-lucide="trees" class="drop-icon"></i>Bush Fire</a></li>
                        <li><a href="{{ route('register') }}"><i data-lucide="help-circle" class="drop-icon"></i>Other Fire Incident</a></li>
                    </ul>
                </li>
                <li class="nav-divider-line"></li>

                <li><a href="{{ route('login') }}" class="nav-link-item">Login</a></li>
                <li><a href="{{ route('register') }}" class="btn-primary btn-sm">Register</a></li>
                <li>
                    <button id="themeToggle" class="theme-toggle" aria-label="Toggle Dark Mode" style="margin-left: 8px;">
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
                    <span>Get Help Now</span>
                    <i data-lucide="arrow-right"></i>
                </a>
                <a href="{{ route('register') }}" class="btn-outline">
                    <i data-lucide="user-plus"></i>
                    <span>Join Now</span>
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

<!-- ===== FEATURE STRIP ===== -->
<div class="feature-strip">
    <div class="feature-strip-inner">
        <div class="strip-item">
            <i data-lucide="zap" class="strip-icon"></i>
            <div><strong>Instant SOS</strong><span>One-tap emergency alert</span></div>
        </div>
        <div class="strip-divider"></div>
        <div class="strip-item">
            <i data-lucide="truck" class="strip-icon"></i>
            <div><strong>Fast Dispatch</strong><span>Nearest responder sent</span></div>
        </div>
        <div class="strip-divider"></div>
        <div class="strip-item">
            <i data-lucide="map-pin" class="strip-icon"></i>
            <div><strong>GPS Tracking</strong><span>Real-time location</span></div>
        </div>
        <div class="strip-divider"></div>
        <div class="strip-item">
            <i data-lucide="hospital" class="strip-icon"></i>
            <div><strong>Hospital Routing</strong><span>Live bed availability</span></div>
        </div>
        <div class="strip-divider"></div>
        <div class="strip-item">
            <i data-lucide="brain-circuit" class="strip-icon"></i>
            <div><strong>AI-Powered</strong><span>Smart emergency routing</span></div>
        </div>
        <div class="strip-divider"></div>
        <div class="strip-item">
            <i data-lucide="shield-check" class="strip-icon"></i>
            <div><strong>24/7 Coverage</strong><span>Always on, always ready</span></div>
        </div>
    </div>
</div>

<!-- ===== STRIP SOS CTA ===== -->
<div class="strip-sos-wrap">
    <a href="{{ route('register') }}" class="strip-sos-btn">
        <span class="strip-sos-ring"></span>
        <span class="strip-sos-ring strip-sos-ring2"></span>
        <span class="strip-sos-label">SOS</span>
    </a>
    <p class="strip-sos-hint">Tap to trigger emergency alert</p>
</div>

<!-- ===== HOW IT WORKS ===== -->
<section class="how-it-works" id="how-it-works">
    <div class="container">
        <div class="text-center fade-up">
            <div class="section-label" style="justify-content:center">Process</div>
            <h2 class="section-title">How ResQLink Works</h2>
            <p class="section-desc center">From alert to resolution in five seamless steps.</p>
        </div>
        <div class="steps-flow fade-up">
            <div class="flow-step">
                <div class="flow-icon"><i data-lucide="bell-ring"></i></div>
                <h4>Alert</h4>
                <p>User triggers SOS through app, USSD, or SMS</p>
            </div>
            <div class="flow-arrow"><i data-lucide="arrow-right"></i></div>
            <div class="flow-step">
                <div class="flow-icon"><i data-lucide="scan-search"></i></div>
                <h4>Analyze</h4>
                <p>AI detects location, emergency type, and urgency</p>
            </div>
            <div class="flow-arrow"><i data-lucide="arrow-right"></i></div>
            <div class="flow-step">
                <div class="flow-icon"><i data-lucide="send"></i></div>
                <h4>Dispatch</h4>
                <p>System alerts nearest available responders</p>
            </div>
            <div class="flow-arrow"><i data-lucide="arrow-right"></i></div>
            <div class="flow-step">
                <div class="flow-icon"><i data-lucide="truck"></i></div>
                <h4>Respond</h4>
                <p>Ambulance, police, fire, or health facility responds</p>
            </div>
            <div class="flow-arrow"><i data-lucide="arrow-right"></i></div>
            <div class="flow-step">
                <div class="flow-icon"><i data-lucide="shield-check"></i></div>
                <h4>Resolve</h4>
                <p>Case tracked until help arrives and incident is closed</p>
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


<!-- ===== KEY FEATURES ===== -->
<section class="features" id="features">
    <div class="container">
        <div class="text-center fade-up">
            <div class="section-label" style="justify-content:center">Features</div>
            <h2 class="section-title">Built for Real Emergencies</h2>
            <p class="section-desc center">Every feature is designed to save lives faster.</p>
        </div>
        <!-- Carousel -->
        <div class="feat-carousel-wrapper fade-up">
            <button class="carousel-btn carousel-prev" id="featPrev" aria-label="Previous">
                <i data-lucide="arrow-left"></i>
            </button>
            <div class="feat-carousel-track-outer">
                <div class="feat-carousel-track" id="featTrack">
                    <div class="feat-card">
                        <div class="f-icon"><i data-lucide="siren" class="lucide-icon"></i></div>
                        <h4>One-Tap SOS</h4>
                        <p>Instantly send an emergency alert with your precise location in one tap.</p>
                    </div>
                    <div class="feat-card">
                        <div class="f-icon"><i data-lucide="map-pin" class="lucide-icon"></i></div>
                        <h4>Live Tracking</h4>
                        <p>Track ambulance or responder movement in real time on the map.</p>
                    </div>
                    <div class="feat-card">
                        <div class="f-icon"><i data-lucide="hospital" class="lucide-icon"></i></div>
                        <h4>Smart Hospital Finder</h4>
                        <p>Route patients to hospitals with available beds and emergency capacity.</p>
                    </div>

                    <div class="feat-card">
                        <div class="f-icon"><i data-lucide="bell-ring" class="lucide-icon"></i></div>
                        <h4>Multi-Channel Alerts</h4>
                        <p>Reach help through App, SMS, USSD, and emergency contact notifications.</p>
                    </div>
                    <div class="feat-card">
                        <div class="f-icon"><i data-lucide="brain-circuit" class="lucide-icon"></i></div>
                        <h4>AI-Powered Routing</h4>
                        <p>Match each emergency to the best available responder automatically.</p>
                    </div>
                    <div class="feat-card">
                        <div class="f-icon"><i data-lucide="clipboard-list" class="lucide-icon"></i></div>
                        <h4>Emergency History</h4>
                        <p>Keep complete records of past alerts, responses, and resolution status.</p>
                    </div>
                    <div class="feat-card">
                        <div class="f-icon"><i data-lucide="layout-dashboard" class="lucide-icon"></i></div>
                        <h4>Responder Dashboard</h4>
                        <p>Help hospitals, ambulances, police, and fire services manage requests efficiently.</p>
                    </div>
                </div>
            </div>
            <button class="carousel-btn carousel-next" id="featNext" aria-label="Next">
                <i data-lucide="arrow-right"></i>
            </button>
        </div>
        <!-- Dots -->
        <div class="carousel-dots" id="featDots"></div>
    </div>
</section>



<!-- ===== IMPACT ===== -->
<section class="impact" id="impact">
    <div class="container">
        <div class="text-center fade-up">
            <div class="section-label" style="justify-content:center">Impact</div>
            <h2 class="section-title">Building Safer Communities</h2>
        </div>
        <div class="impact-grid fade-up" style="grid-template-columns: repeat(4, 1fr);">
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
        <div class="contact-wrapper fade-up" style="grid-template-columns: 1fr;">
            <form class="contact-form" id="contact-form" style="max-width: 680px; margin: 0 auto; width: 100%;">
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
<a href="#market">Business Model</a>
                <a href="#impact">Impact</a>
            </div>
            <div class="footer-col">
                <h5>Connect</h5>
                <a href="#contact">Contact Us</a>
            </div>
        </div>
        <div class="footer-bottom">
            &copy; {{ date('Y') }} ResQLink. All rights reserved. Saving lives through instant connection.
        </div>
    </div>
</footer>

<script src="/js/landing.js"></script>
<script src="/js/chat.js"></script>

<!-- Support Widget -->
<div class="support-trigger" id="supportTrigger" style="position: fixed; bottom: 30px; left: 30px; width: 60px; height: 60px; background: var(--dark); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--white); cursor: pointer; box-shadow: 0 10px 30px rgba(0,0,0,0.3); z-index: 5000; transition: transform 0.3s;">
    <i data-lucide="mail"></i>
</div>

<div class="support-window" id="supportWindow" style="position: fixed; bottom: 100px; left: 30px; width: 350px; background: var(--glass); border: 1px solid var(--glass-border); border-radius: 20px; display: none; flex-direction: column; overflow: hidden; z-index: 5000; box-shadow: 0 20px 50px rgba(0,0,0,0.5); backdrop-filter: blur(20px);">
    <div style="background: rgba(0,0,0,0.5); padding: 15px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--glass-border);">
        <h4 style="margin: 0; color: white;">Contact Admin</h4>
        <button onclick="toggleSupport()" style="background: transparent; border: none; color: var(--grey); cursor: pointer;"><i data-lucide="x" style="width: 20px;"></i></button>
    </div>
    <div style="padding: 20px;">
        @if(session('success'))
            <div style="color: #22c55e; margin-bottom: 15px; font-size: 0.9rem;">{{ session('success') }}</div>
        @endif
        <form action="{{ route('support.message') }}" method="POST">
            @csrf
            @guest
                <input type="text" name="name" placeholder="Your Name" required style="width: 100%; margin-bottom: 10px; padding: 10px; border-radius: 8px; border: 1px solid var(--glass-border); background: rgba(255,255,255,0.05); color: white;">
                <input type="email" name="email" placeholder="Your Email" required style="width: 100%; margin-bottom: 10px; padding: 10px; border-radius: 8px; border: 1px solid var(--glass-border); background: rgba(255,255,255,0.05); color: white;">
            @endguest
            <textarea name="message" placeholder="How can we help you?" required rows="4" style="width: 100%; margin-bottom: 10px; padding: 10px; border-radius: 8px; border: 1px solid var(--glass-border); background: rgba(255,255,255,0.05); color: white; resize: none;"></textarea>
            <button type="submit" class="btn-primary" style="width: 100%; border: none; padding: 12px; border-radius: 8px; cursor: pointer;">Send Message</button>
        </form>
    </div>
</div>

<script>
    document.getElementById('supportTrigger').addEventListener('click', toggleSupport);
    function toggleSupport() {
        const w = document.getElementById('supportWindow');
        w.style.display = w.style.display === 'flex' ? 'none' : 'flex';
    }
    
    // Auto open if there is a success message in session
    @if(session('success'))
        toggleSupport();
    @endif
</script>
<script>lucide.createIcons();</script>
</body>
</html>
