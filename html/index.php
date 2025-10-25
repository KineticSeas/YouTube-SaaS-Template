<?php
/**
 * TodoTracker - Landing Page / Home
 */

require_once 'includes/session.php';

// If user is logged in, redirect to dashboard
if (isLoggedIn()) {
    header('Location: /dashboard.php');
    exit;
}

$pageTitle = 'Welcome to TodoTracker';
require_once 'includes/header.php';
?>

<div id="landing-container" class="container py-5">
    <div id="landing-hero" class="row align-items-center mb-5">
        <div id="hero-content" class="col-lg-6">
            <h1 id="hero-title" class="display-3 fw-bold mb-4">
                <i class="bi bi-check-circle-fill text-primary"></i> TodoTracker
            </h1>
            <p id="hero-subtitle" class="lead mb-4">
                Your basic SaaS template for a traditional LAMP stack using Bootstrap, HTMX, and Alpine.js
            </p>
            <div id="hero-buttons" class="d-grid gap-3 d-sm-flex">
                <a id="get-started-btn" href="/auth/register.php" class="btn btn-primary btn-lg px-4">
                    <i class="bi bi-person-plus"></i> Get Started
                </a>
                <a id="login-btn" href="/auth/login.php" class="btn btn-outline-primary btn-lg px-4">
                    <i class="bi bi-box-arrow-in-right"></i> Login
                </a>
            </div>
        </div>
        <div id="hero-image" class="col-lg-6 text-center mt-5 mt-lg-0">
            <div id="hero-card" class="card shadow-lg border-0">
                <div class="card-body p-4">
                    <i class="bi bi-list-check text-primary" style="font-size: 8rem;"></i>
                    <h3 class="mt-3">Organize Everything</h3>
                    <p class="text-muted">Stay on top of your tasks with powerful views</p>
                </div>
            </div>
        </div>
    </div>

    <div id="features-section" class="row g-4 mb-5">
        <div class="col-12">
            <h2 id="features-title" class="text-center mb-5">Powerful Features</h2>
        </div>

        <div id="feature-dashboard" class="col-md-4">
            <div class="card h-100 text-center border-0 shadow-sm">
                <div class="card-body p-4">
                    <i class="bi bi-speedometer2 text-primary" style="font-size: 3rem;"></i>
                    <h4 class="mt-3">Dashboard</h4>
                    <p class="text-muted">
                        Get a bird's eye view of all your tasks with statistics and insights.
                    </p>
                </div>
            </div>
        </div>

        <div id="feature-kanban" class="col-md-4">
            <div class="card h-100 text-center border-0 shadow-sm">
                <div class="card-body p-4">
                    <i class="bi bi-columns text-primary" style="font-size: 3rem;"></i>
                    <h4 class="mt-3">Kanban Board</h4>
                    <p class="text-muted">
                        Visualize your workflow with drag-and-drop task management.
                    </p>
                </div>
            </div>
        </div>

        <div id="feature-calendar" class="col-md-4">
            <div class="card h-100 text-center border-0 shadow-sm">
                <div class="card-body p-4">
                    <i class="bi bi-calendar3 text-primary" style="font-size: 3rem;"></i>
                    <h4 class="mt-3">Calendar View</h4>
                    <p class="text-muted">
                        See your tasks organized by due dates in an intuitive calendar.
                    </p>
                </div>
            </div>
        </div>

        <div id="feature-categories" class="col-md-4">
            <div class="card h-100 text-center border-0 shadow-sm">
                <div class="card-body p-4">
                    <i class="bi bi-tags text-primary" style="font-size: 3rem;"></i>
                    <h4 class="mt-3">Categories</h4>
                    <p class="text-muted">
                        Organize tasks with custom categories and color-coded tags.
                    </p>
                </div>
            </div>
        </div>

        <div id="feature-priority" class="col-md-4">
            <div class="card h-100 text-center border-0 shadow-sm">
                <div class="card-body p-4">
                    <i class="bi bi-flag text-primary" style="font-size: 3rem;"></i>
                    <h4 class="mt-3">Priority Levels</h4>
                    <p class="text-muted">
                        Mark tasks as low, medium, or high priority to stay focused.
                    </p>
                </div>
            </div>
        </div>

        <div id="feature-secure" class="col-md-4">
            <div class="card h-100 text-center border-0 shadow-sm">
                <div class="card-body p-4">
                    <i class="bi bi-shield-check text-primary" style="font-size: 3rem;"></i>
                    <h4 class="mt-3">Secure & Private</h4>
                    <p class="text-muted">
                        Your data is encrypted and protected with enterprise-grade security.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div id="cta-section" class="row">
        <div id="cta-card" class="col-12">
            <div class="card bg-primary text-white text-center border-0 shadow-lg">
                <div class="card-body p-5">
                    <h2 id="cta-title" class="mb-3">Ready to Get Organized?</h2>
                    <p id="cta-text" class="lead mb-4">
                        Join thousands of users who trust TodoTracker to manage their tasks.
                    </p>
                    <div id="cta-buttons" class="d-grid gap-3 d-sm-flex justify-content-center">
                        <a id="cta-register" href="/auth/register.php" class="btn btn-light btn-lg px-5">
                            <i class="bi bi-person-plus"></i> Sign Up Free
                        </a>
                        <a id="cta-demo" href="/auth/login.php" class="btn btn-outline-light btn-lg px-5">
                            <i class="bi bi-play-circle"></i> Try Demo
                        </a>
                    </div>
                    <p id="demo-credentials" class="mt-4 mb-0 text-white-50 small">
                        Demo: demo@todotracker.com / Demo123!
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
