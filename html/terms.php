<?php
/**
 * TodoTracker - Terms of Service
 */

require_once 'includes/session.php';

$pageTitle = 'Terms of Service - TodoTracker';
require_once 'includes/header.php';
?>

<div id="terms-container" class="container py-5">
    <div id="terms-header" class="row mb-4">
        <div id="terms-header-content" class="col-12">
            <h1 id="terms-title" class="display-4 fw-bold mb-3">
                <i class="bi bi-file-text text-primary"></i> Terms of Service
            </h1>
            <p id="terms-last-updated" class="text-muted">Last Updated: <?php echo date('F j, Y'); ?></p>
        </div>
    </div>

    <div id="terms-content" class="row">
        <div id="terms-content-inner" class="col-lg-10 mx-auto">
            <div id="terms-card" class="card shadow-sm border-0">
                <div class="card-body p-4 p-md-5">

                    <section id="terms-intro" class="mb-5">
                        <p class="lead">
                            Welcome to TodoTracker. These Terms of Service ("Terms") govern your access to and use of our task management application. By accessing or using TodoTracker, you agree to be bound by these Terms. If you do not agree to these Terms, please do not use our service.
                        </p>
                    </section>

                    <section id="terms-acceptance" class="mb-5">
                        <h2 class="h3 mb-3">1. Acceptance of Terms</h2>
                        <p>
                            By creating an account or using TodoTracker, you acknowledge that you have read, understood, and agree to be bound by these Terms and our Privacy Policy. These Terms apply to all users of the service, including visitors, registered users, and contributors.
                        </p>
                    </section>

                    <section id="terms-eligibility" class="mb-5">
                        <h2 class="h3 mb-3">2. Eligibility</h2>
                        <p>You must meet the following criteria to use TodoTracker:</p>
                        <ul>
                            <li>You must be at least 13 years of age</li>
                            <li>You must be able to form a binding contract with us</li>
                            <li>You must not be prohibited from using our service under applicable laws</li>
                            <li>You must provide accurate and complete registration information</li>
                        </ul>
                        <p>
                            If you are using TodoTracker on behalf of an organization, you represent that you have the authority to bind that organization to these Terms.
                        </p>
                    </section>

                    <section id="terms-account" class="mb-5">
                        <h2 class="h3 mb-3">3. Account Registration and Security</h2>

                        <h3 id="terms-account-creation" class="h5 mb-3">Account Creation</h3>
                        <p>To use certain features of TodoTracker, you must create an account. You agree to:</p>
                        <ul>
                            <li>Provide accurate, current, and complete information during registration</li>
                            <li>Maintain and promptly update your account information</li>
                            <li>Maintain the security of your password and account</li>
                            <li>Accept responsibility for all activities that occur under your account</li>
                            <li>Immediately notify us of any unauthorized use of your account</li>
                        </ul>

                        <h3 id="terms-account-responsibility" class="h5 mb-3 mt-4">Account Responsibility</h3>
                        <p>
                            You are solely responsible for maintaining the confidentiality of your account credentials and for all activities that occur under your account. TodoTracker will not be liable for any loss or damage arising from your failure to comply with these security obligations.
                        </p>
                    </section>

                    <section id="terms-acceptable-use" class="mb-5">
                        <h2 class="h3 mb-3">4. Acceptable Use Policy</h2>
                        <p>You agree not to:</p>
                        <ul>
                            <li>Use the service for any illegal purpose or in violation of any laws</li>
                            <li>Violate or infringe upon the rights of others, including intellectual property rights</li>
                            <li>Transmit any harmful code, viruses, or malicious software</li>
                            <li>Attempt to gain unauthorized access to our systems or other users' accounts</li>
                            <li>Interfere with or disrupt the service or servers</li>
                            <li>Use automated means to access the service without our permission</li>
                            <li>Impersonate any person or entity or misrepresent your affiliation</li>
                            <li>Collect or store personal data about other users without permission</li>
                            <li>Use the service to send spam or unsolicited communications</li>
                            <li>Reverse engineer, decompile, or disassemble any part of the service</li>
                        </ul>
                    </section>

                    <section id="terms-content" class="mb-5">
                        <h2 class="h3 mb-3">5. User Content</h2>

                        <h3 id="terms-content-ownership" class="h5 mb-3">Your Content</h3>
                        <p>
                            You retain all rights to the content you create, upload, or store in TodoTracker ("User Content"). By using our service, you grant us a limited license to host, store, and display your User Content solely for the purpose of providing the service to you.
                        </p>

                        <h3 id="terms-content-responsibility" class="h5 mb-3 mt-4">Content Responsibility</h3>
                        <p>
                            You are solely responsible for your User Content and the consequences of posting or publishing it. You represent and warrant that you own or have the necessary rights to use and authorize us to use your User Content as described in these Terms.
                        </p>

                        <h3 id="terms-content-removal" class="h5 mb-3 mt-4">Content Removal</h3>
                        <p>
                            We reserve the right to remove or disable access to any User Content that we determine, in our sole discretion, violates these Terms or is otherwise objectionable.
                        </p>
                    </section>

                    <section id="terms-intellectual-property" class="mb-5">
                        <h2 class="h3 mb-3">6. Intellectual Property Rights</h2>
                        <p>
                            TodoTracker and its original content, features, and functionality are owned by us and are protected by international copyright, trademark, patent, trade secret, and other intellectual property laws. You may not copy, modify, distribute, sell, or lease any part of our service without our express written permission.
                        </p>
                    </section>

                    <section id="terms-subscription" class="mb-5">
                        <h2 class="h3 mb-3">7. Subscription and Payment</h2>

                        <h3 id="terms-subscription-plans" class="h5 mb-3">Subscription Plans</h3>
                        <p>
                            TodoTracker may offer free and paid subscription plans. The features, pricing, and terms of each plan will be described on our website.
                        </p>

                        <h3 id="terms-billing" class="h5 mb-3 mt-4">Billing and Renewal</h3>
                        <p>
                            Paid subscriptions automatically renew at the end of each billing period unless you cancel before the renewal date. You authorize us to charge your payment method for the applicable fees. All fees are non-refundable except as required by law.
                        </p>

                        <h3 id="terms-price-changes" class="h5 mb-3 mt-4">Price Changes</h3>
                        <p>
                            We reserve the right to modify our pricing. We will provide reasonable notice of any price changes and give you the opportunity to cancel your subscription before the new price takes effect.
                        </p>
                    </section>

                    <section id="terms-termination" class="mb-5">
                        <h2 class="h3 mb-3">8. Termination</h2>

                        <h3 id="terms-termination-by-you" class="h5 mb-3">Termination by You</h3>
                        <p>
                            You may terminate your account at any time through your account settings or by contacting us. Upon termination, your right to use the service will immediately cease.
                        </p>

                        <h3 id="terms-termination-by-us" class="h5 mb-3 mt-4">Termination by Us</h3>
                        <p>
                            We reserve the right to suspend or terminate your account and access to the service at any time, with or without cause, with or without notice. Reasons for termination may include violation of these Terms, fraudulent activity, or prolonged inactivity.
                        </p>

                        <h3 id="terms-effect-termination" class="h5 mb-3 mt-4">Effect of Termination</h3>
                        <p>
                            Upon termination, we may delete your account and User Content. We are not obligated to retain or provide you with copies of your User Content after termination, except as required by law.
                        </p>
                    </section>

                    <section id="terms-disclaimers" class="mb-5">
                        <h2 class="h3 mb-3">9. Disclaimers</h2>
                        <div id="terms-disclaimers-notice" class="alert alert-warning border-0">
                            <p class="mb-2">
                                <strong>THE SERVICE IS PROVIDED "AS IS" AND "AS AVAILABLE" WITHOUT WARRANTIES OF ANY KIND.</strong>
                            </p>
                            <p class="mb-0">
                                We disclaim all warranties, express or implied, including warranties of merchantability, fitness for a particular purpose, and non-infringement. We do not warrant that the service will be uninterrupted, secure, or error-free.
                            </p>
                        </div>
                    </section>

                    <section id="terms-limitation-liability" class="mb-5">
                        <h2 class="h3 mb-3">10. Limitation of Liability</h2>
                        <div id="terms-liability-notice" class="alert alert-warning border-0">
                            <p class="mb-2">
                                <strong>TO THE MAXIMUM EXTENT PERMITTED BY LAW, TODOTRACKER SHALL NOT BE LIABLE FOR ANY INDIRECT, INCIDENTAL, SPECIAL, CONSEQUENTIAL, OR PUNITIVE DAMAGES.</strong>
                            </p>
                            <p class="mb-0">
                                Our total liability for any claims arising out of or relating to these Terms or the service shall not exceed the amount you paid us in the twelve (12) months prior to the claim, or $100, whichever is greater.
                            </p>
                        </div>
                    </section>

                    <section id="terms-indemnification" class="mb-5">
                        <h2 class="h3 mb-3">11. Indemnification</h2>
                        <p>
                            You agree to indemnify, defend, and hold harmless TodoTracker and its officers, directors, employees, and agents from any claims, liabilities, damages, losses, and expenses arising out of or in any way connected with:
                        </p>
                        <ul>
                            <li>Your access to or use of the service</li>
                            <li>Your User Content</li>
                            <li>Your violation of these Terms</li>
                            <li>Your violation of any third-party rights</li>
                        </ul>
                    </section>

                    <section id="terms-dispute-resolution" class="mb-5">
                        <h2 class="h3 mb-3">12. Dispute Resolution</h2>

                        <h3 id="terms-governing-law" class="h5 mb-3">Governing Law</h3>
                        <p>
                            These Terms shall be governed by and construed in accordance with the laws of the jurisdiction in which TodoTracker operates, without regard to its conflict of law provisions.
                        </p>

                        <h3 id="terms-arbitration" class="h5 mb-3 mt-4">Arbitration</h3>
                        <p>
                            Any dispute arising from these Terms or the service shall be resolved through binding arbitration in accordance with the rules of the applicable arbitration association. You waive your right to participate in a class action lawsuit or class-wide arbitration.
                        </p>
                    </section>

                    <section id="terms-general" class="mb-5">
                        <h2 class="h3 mb-3">13. General Provisions</h2>

                        <h3 id="terms-changes" class="h5 mb-3">Changes to Terms</h3>
                        <p>
                            We reserve the right to modify these Terms at any time. We will notify you of material changes by posting the updated Terms with a new "Last Updated" date. Your continued use of the service after changes constitutes acceptance of the modified Terms.
                        </p>

                        <h3 id="terms-severability" class="h5 mb-3 mt-4">Severability</h3>
                        <p>
                            If any provision of these Terms is found to be invalid or unenforceable, the remaining provisions will remain in full force and effect.
                        </p>

                        <h3 id="terms-waiver" class="h5 mb-3 mt-4">Waiver</h3>
                        <p>
                            Our failure to enforce any right or provision of these Terms will not be considered a waiver of those rights.
                        </p>

                        <h3 id="terms-entire-agreement" class="h5 mb-3 mt-4">Entire Agreement</h3>
                        <p>
                            These Terms, together with our Privacy Policy, constitute the entire agreement between you and TodoTracker regarding the service.
                        </p>

                        <h3 id="terms-assignment" class="h5 mb-3 mt-4">Assignment</h3>
                        <p>
                            You may not assign or transfer these Terms without our prior written consent. We may assign or transfer these Terms without restriction.
                        </p>
                    </section>

                    <section id="terms-contact" class="mb-4">
                        <h2 class="h3 mb-3">14. Contact Information</h2>
                        <p>
                            If you have any questions or concerns about these Terms of Service, please contact us:
                        </p>
                        <div id="terms-contact-info" class="alert alert-info border-0">
                            <p class="mb-1"><strong>Email:</strong> legal@todotracker.com</p>
                            <p class="mb-0"><strong>Support:</strong> <a href="/help.php">Visit our Help Center</a></p>
                        </div>
                    </section>

                    <section id="terms-acknowledgment" class="mb-4">
                        <div id="terms-acknowledgment-box" class="alert alert-primary border-0">
                            <p class="mb-0">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>By using TodoTracker, you acknowledge that you have read and understood these Terms of Service and agree to be bound by them.</strong>
                            </p>
                        </div>
                    </section>

                </div>
            </div>

            <div id="terms-back-button" class="text-center mt-4">
                <a href="/" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-left"></i> Back to Home
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
