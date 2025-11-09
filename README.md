# WPX Memberships: TutorLMS Pending Student Approval

[cite_start]A powerful and lightweight WordPress plugin that enforces **admin approval** for all new student signups before they gain access to TutorLMS courses[cite: 1, 2].

---

## 🚀 Features

* [cite_start]**Pending Role Assignment:** Automatically assigns a custom role, `tutor_pending_student`, to all new user registrations[cite: 1].
* **Admin Approval Gateway:** Users require an administrator to manually approve their profile to switch to the full `tutor_student` role and gain access.
* **Access Restriction:** Blocks access to all TutorLMS course-related content for pending students, including:
    * Individual Courses (`tutor_course`, `courses`)
    * Lessons (`tutor_lesson`, `lesson`)
    * Quizzes (`tutor_quiz`)
    * Assignments (`tutor_assignments`)
    * [cite_start]Course Archives[cite: 1].
* **Dedicated Admin Page:** Adds a **"Tutor Approvals"** menu item in the WordPress admin dashboard for easy management and one-click approval of pending students.
* **Email Notification:** Sends an automated email notification to the user once their account has been approved.
* **Frontend Notice:** Displays a helpful message on the frontend to logged-in, pending users who attempt to access blocked content, informing them their account is awaiting approval.
* **User Status Column:** Adds a **"Tutor Status"** column to the default WordPress Users table for quick status verification.

---

## ⚙️ Installation

### Standard Installation

1.  **Download:** Download the plugin files (or clone the repository).
2.  **Upload:** Upload the `wpx-memberships` folder to your `/wp-content/plugins/` directory.
3.  **Activate:** Go to **Plugins** in your WordPress admin dashboard and **Activate** the "WPX Memberships" plugin.

### Usage

1.  After activation, all **new user signups** will automatically be set to the **"Pending Student"** role.
2.  To view and approve new signups, navigate to the **Tutor Approvals** page in your WordPress admin menu (found below the 'Users' menu).
3.  On the approvals page, you will see a list of all pending students. Click the **"Approve"** button next to the user's name to grant them the `tutor_student` role and full course access.

---

## 🛠️ Technical Details

| Detail | Value |
| :--- | :--- |
| **Compatible With** | [cite_start]TutorLMS System [cite: 2] |
| **Requires at least** | [cite_start]6.0 [cite: 1] |
| **Tested up to** | [cite_start]6.6 [cite: 1] |
| **Requires PHP** | [cite_start]7.4 [cite: 1] |
| **Stable tag** | [cite_start]1.0.0 [cite: 1] |
| **License** | [cite_start]GPLv2 or later [cite: 1] |

---

## 🤝 Contribution

Feel free to open an issue or submit a pull request if you find any bugs or have suggestions for improvements!
