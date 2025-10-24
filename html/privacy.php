<?php
/**
 * TodoTracker - Privacy Policy
 */

require_once 'includes/session.php';

$pageTitle = 'Privacy Policy - TodoTracker';
require_once 'includes/header.php';
?>

<div id="privacy-container" class="container py-5">
    <div id="privacy-header" class="row mb-4">
        <div id="privacy-header-content" class="col-12">
            <h1 id="privacy-title" class="display-4 fw-bold mb-3">
                <i class="bi bi-shield-check text-primary"></i> Privacy Policy
            </h1>
            <p id="privacy-last-updated" class="text-muted">Last Updated: <?php echo date('F j, Y'); ?></p>
        </div>
    </div>

    <div id="privacy-content" class="row">
        <div id="privacy-content-inner" class="col-lg-10 mx-auto">
            <div id="privacy-card" class="card shadow-sm border-0">
                <div class="card-body p-4 p-md-5">

                    <section id="privacy-intro" class="mb-5">
                        <p class="lead">
                            TodoTracker ("we", "our", or "us") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our task management application.
                        </p>
                    </section>

                    <section id="privacy-information-collection" class="mb-5">
                        <h2 class="h3 mb-3">1. Information We Collect</h2>

                        <h3 id="privacy-personal-info" class="h5 mb-3">Personal Information</h3>
                        <p>We collect information that you provide directly to us, including:</p>
                        <ul>
                            <li>Name and email address when you create an account</li>
                            <li>Profile information such as username and profile picture</li>
                            <li>Task data, notes, and other content you create within the application</li>
                            <li>Communications you send to us</li>
                        </ul>

                        <h3 id="privacy-usage-info" class="h5 mb-3 mt-4">Usage Information</h3>
                        <p>We automatically collect certain information about your device and how you interact with our services:</p>
                        <ul>
                            <li>Log data (IP address, browser type, operating system)</li>
                            <li>Device information and identifiers</li>
                            <li>Usage patterns and preferences</li>
                            <li>Cookies and similar tracking technologies</li>
                        </ul>
                    </section>

                    <section id="privacy-how-we-use" class="mb-5">
                        <h2 class="h3 mb-3">2. How We Use Your Information</h2>
                        <p>We use the information we collect to:</p>
                        <ul>
                            <li>Provide, maintain, and improve our services</li>
                            <li>Create and manage your account</li>
                            <li>Process your transactions and send related information</li>
                            <li>Send you technical notices, updates, and security alerts</li>
                            <li>Respond to your comments, questions, and customer service requests</li>
                            <li>Monitor and analyze trends, usage, and activities</li>
                            <li>Detect, prevent, and address technical issues and fraudulent activity</li>
                            <li>Personalize and improve your experience</li>
                        </ul>
                    </section>

                    <section id="privacy-sharing" class="mb-5">
                        <h2 class="h3 mb-3">3. Information Sharing and Disclosure</h2>
                        <p>We do not sell your personal information. We may share your information only in the following circumstances:</p>
                        <ul>
                            <li><strong>With your consent:</strong> We may share your information when you give us explicit permission</li>
                            <li><strong>Service providers:</strong> We may share information with third-party vendors who perform services on our behalf</li>
                            <li><strong>Legal requirements:</strong> We may disclose information if required by law or in response to legal requests</li>
                            <li><strong>Business transfers:</strong> Information may be transferred in connection with a merger, acquisition, or sale of assets</li>
                            <li><strong>Protection:</strong> We may share information to protect the rights, property, and safety of TodoTracker, our users, or others</li>
                        </ul>
                    </section>

                    <section id="privacy-data-security" class="mb-5">
                        <h2 class="h3 mb-3">4. Data Security</h2>
                        <p>
                            We implement appropriate technical and organizational measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction. However, no method of transmission over the Internet or electronic storage is 100% secure, and we cannot guarantee absolute security.
                        </p>
                    </section>

                    <section id="privacy-retention" class="mb-5">
                        <h2 class="h3 mb-3">5. Data Retention</h2>
                        <p>
                            We retain your personal information for as long as necessary to fulfill the purposes outlined in this Privacy Policy, unless a longer retention period is required or permitted by law. When you delete your account, we will delete or anonymize your personal information.
                        </p>
                    </section>

                    <section id="privacy-rights" class="mb-5">
                        <h2 class="h3 mb-3">6. Your Rights and Choices</h2>
                        <p>Depending on your location, you may have certain rights regarding your personal information:</p>
                        <ul>
                            <li><strong>Access:</strong> Request access to your personal information</li>
                            <li><strong>Correction:</strong> Request correction of inaccurate or incomplete information</li>
                            <li><strong>Deletion:</strong> Request deletion of your personal information</li>
                            <li><strong>Data portability:</strong> Request a copy of your data in a machine-readable format</li>
                            <li><strong>Opt-out:</strong> Opt out of marketing communications</li>
                            <li><strong>Cookie preferences:</strong> Manage your cookie preferences through your browser settings</li>
                        </ul>
                    </section>

                    <section id="privacy-cookies" class="mb-5">
                        <h2 class="h3 mb-3">7. Cookies and Tracking Technologies</h2>
                        <p>
                            We use cookies and similar tracking technologies to collect and track information about your activity on our service. You can instruct your browser to refuse all cookies or to indicate when a cookie is being sent. However, if you do not accept cookies, you may not be able to use some portions of our service.
                        </p>
                    </section>

                    <section id="privacy-children" class="mb-5">
                        <h2 class="h3 mb-3">8. Children's Privacy</h2>
                        <p>
                            Our service is not directed to individuals under the age of 13. We do not knowingly collect personal information from children under 13. If you become aware that a child has provided us with personal information, please contact us, and we will take steps to delete such information.
                        </p>
                    </section>

                    <section id="privacy-international" class="mb-5">
                        <h2 class="h3 mb-3">9. International Data Transfers</h2>
                        <p>
                            Your information may be transferred to and maintained on computers located outside of your state, province, country, or other governmental jurisdiction where data protection laws may differ. By using our service, you consent to the transfer of your information to our facilities and those third parties with whom we share it as described in this policy.
                        </p>
                    </section>

                    <section id="privacy-changes" class="mb-5">
                        <h2 class="h3 mb-3">10. Changes to This Privacy Policy</h2>
                        <p>
                            We may update this Privacy Policy from time to time. We will notify you of any changes by posting the new Privacy Policy on this page and updating the "Last Updated" date. You are advised to review this Privacy Policy periodically for any changes.
                        </p>
                    </section>

                    <section id="privacy-contact" class="mb-4">
                        <h2 class="h3 mb-3">11. Contact Us</h2>
                        <p>
                            If you have any questions or concerns about this Privacy Policy or our data practices, please contact us:
                        </p>
                        <div id="privacy-contact-info" class="alert alert-info border-0">
                            <p class="mb-1"><strong>Email:</strong> privacy@todotracker.com</p>
                            <p class="mb-0"><strong>Support:</strong> <a href="/help.php">Visit our Help Center</a></p>
                        </div>
                    </section>

                </div>
            </div>

            <div id="privacy-back-button" class="text-center mt-4">
                <a href="/" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-left"></i> Back to Home
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
