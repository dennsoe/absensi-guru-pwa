# 🎨 DESIGN SYSTEM GUIDE - SIAG NEKAS

## 📋 Table of Contents

1. [Overview](#overview)
2. [Color System](#color-system)
3. [Typography](#typography)
4. [Spacing System](#spacing-system)
5. [Components](#components)
6. [Layout Structure](#layout-structure)
7. [Animations](#animations)
8. [Responsive Design](#responsive-design)
9. [Implementation Guide](#implementation-guide)

---

## 📖 Overview

Design system ini diadaptasi dari **Coffee Monster Drink App (home-02.html)** untuk aplikasi **Sistem Informasi Absensi Guru (SIAG) SMK Negeri Kasomalang**.

### Design Principles:

-   ✅ **Mobile-First** - Optimized untuk smartphone
-   ✅ **Clean & Modern** - Minimalist dengan fokus konten
-   ✅ **User-Friendly** - Intuitive navigation
-   ✅ **Professional** - Sesuai konteks pendidikan
-   ✅ **Performant** - Fast loading & smooth animations

---

## 🎨 Color System

### Primary Colors (Professional Blue)

```css
:root {
    /* Primary - Blue Theme */
    --primary-50: #eff6ff;
    --primary-100: #dbeafe;
    --primary-200: #bfdbfe;
    --primary-300: #93c5fd;
    --primary-400: #60a5fa;
    --primary-500: #3b82f6;
    --primary-600: #2563eb; /* Main Primary */
    --primary-700: #1d4ed8;
    --primary-800: #1e40af;
    --primary-900: #1e3a8a;
}
```

### Secondary Colors (Education Green)

```css
:root {
    /* Secondary - Green Theme */
    --secondary-50: #ecfdf5;
    --secondary-100: #d1fae5;
    --secondary-200: #a7f3d0;
    --secondary-300: #6ee7b7;
    --secondary-400: #34d399;
    --secondary-500: #10b981; /* Main Secondary */
    --secondary-600: #059669;
    --secondary-700: #047857;
    --secondary-800: #065f46;
    --secondary-900: #064e3b;
}
```

### Accent Colors (Energetic Orange)

```css
:root {
    /* Accent - Amber/Orange */
    --accent-50: #fffbeb;
    --accent-100: #fef3c7;
    --accent-200: #fde68a;
    --accent-300: #fcd34d;
    --accent-400: #fbbf24;
    --accent-500: #f59e0b; /* Main Accent */
    --accent-600: #d97706;
    --accent-700: #b45309;
    --accent-800: #92400e;
    --accent-900: #78350f;
}
```

### Status Colors

```css
:root {
    /* Status Indicators */
    --success: #10b981; /* Hadir */
    --warning: #f59e0b; /* Terlambat */
    --danger: #ef4444; /* Alpha */
    --info: #3b82f6; /* Izin */
    --sakit: #8b5cf6; /* Sakit (Purple) */
    --cuti: #6366f1; /* Cuti (Indigo) */
}
```

### Neutral Colors

```css
:root {
    /* Background Colors */
    --bg-primary: #ffffff;
    --bg-secondary: #f9fafb;
    --bg-tertiary: #f3f4f6;
    --bg-dark: #111827;

    /* Text Colors */
    --text-primary: #111827; /* Gray 900 */
    --text-secondary: #6b7280; /* Gray 500 */
    --text-tertiary: #9ca3af; /* Gray 400 */
    --text-disabled: #d1d5db; /* Gray 300 */
    --text-inverse: #ffffff;

    /* Border Colors */
    --border-light: #f3f4f6; /* Gray 100 */
    --border-medium: #e5e7eb; /* Gray 200 */
    --border-dark: #d1d5db; /* Gray 300 */
}
```

### Shadow Colors

```css
:root {
    /* Shadows */
    --shadow-xs: 0 1px 2px rgba(0, 0, 0, 0.05);
    --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.1), 0 1px 2px rgba(0, 0, 0, 0.06);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);

    /* Colored Shadows */
    --shadow-primary: 0 4px 12px rgba(37, 99, 235, 0.25);
    --shadow-success: 0 4px 12px rgba(16, 185, 129, 0.25);
    --shadow-warning: 0 4px 12px rgba(245, 158, 11, 0.25);
    --shadow-danger: 0 4px 12px rgba(239, 68, 68, 0.25);
}
```

---

## 📝 Typography

### Font Family

```css
:root {
    /* Primary Font Stack */
    --font-primary: "Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto,
        Oxygen, Ubuntu, Cantarell, sans-serif;

    /* Alternative: Poppins (more rounded, friendly) */
    --font-secondary: "Poppins", -apple-system, BlinkMacSystemFont, sans-serif;

    /* Monospace (for code, time) */
    --font-mono: "SF Mono", Monaco, "Cascadia Code", "Roboto Mono", Consolas,
        monospace;
}
```

### Font Sizes

```css
:root {
    /* Font Size Scale */
    --text-xs: 0.75rem; /* 12px */
    --text-sm: 0.875rem; /* 14px */
    --text-base: 1rem; /* 16px */
    --text-lg: 1.125rem; /* 18px */
    --text-xl: 1.25rem; /* 20px */
    --text-2xl: 1.5rem; /* 24px */
    --text-3xl: 1.875rem; /* 30px */
    --text-4xl: 2.25rem; /* 36px */
    --text-5xl: 3rem; /* 48px */
}
```

### Font Weights

```css
:root {
    --font-thin: 100;
    --font-light: 300;
    --font-normal: 400;
    --font-medium: 500;
    --font-semibold: 600;
    --font-bold: 700;
    --font-extrabold: 800;
    --font-black: 900;
}
```

### Line Heights

```css
:root {
    --leading-none: 1;
    --leading-tight: 1.25;
    --leading-snug: 1.375;
    --leading-normal: 1.5;
    --leading-relaxed: 1.625;
    --leading-loose: 2;
}
```

### Typography Classes

```css
/* Headings */
.heading-1 {
    font-size: var(--text-4xl);
    font-weight: var(--font-bold);
    line-height: var(--leading-tight);
    color: var(--text-primary);
}

.heading-2 {
    font-size: var(--text-3xl);
    font-weight: var(--font-bold);
    line-height: var(--leading-tight);
    color: var(--text-primary);
}

.heading-3 {
    font-size: var(--text-2xl);
    font-weight: var(--font-semibold);
    line-height: var(--leading-snug);
    color: var(--text-primary);
}

.heading-4 {
    font-size: var(--text-xl);
    font-weight: var(--font-semibold);
    line-height: var(--leading-snug);
    color: var(--text-primary);
}

/* Body Text */
.body-large {
    font-size: var(--text-lg);
    font-weight: var(--font-normal);
    line-height: var(--leading-relaxed);
    color: var(--text-primary);
}

.body-base {
    font-size: var(--text-base);
    font-weight: var(--font-normal);
    line-height: var(--leading-normal);
    color: var(--text-primary);
}

.body-small {
    font-size: var(--text-sm);
    font-weight: var(--font-normal);
    line-height: var(--leading-normal);
    color: var(--text-secondary);
}

.body-tiny {
    font-size: var(--text-xs);
    font-weight: var(--font-normal);
    line-height: var(--leading-normal);
    color: var(--text-tertiary);
}
```

---

## 📏 Spacing System

### Base Unit: 4px

```css
:root {
    /* Spacing Scale (rem) */
    --space-0: 0;
    --space-1: 0.25rem; /* 4px */
    --space-2: 0.5rem; /* 8px */
    --space-3: 0.75rem; /* 12px */
    --space-4: 1rem; /* 16px */
    --space-5: 1.25rem; /* 20px */
    --space-6: 1.5rem; /* 24px */
    --space-8: 2rem; /* 32px */
    --space-10: 2.5rem; /* 40px */
    --space-12: 3rem; /* 48px */
    --space-16: 4rem; /* 64px */
    --space-20: 5rem; /* 80px */
    --space-24: 6rem; /* 96px */
}
```

### Layout Spacing

```css
:root {
    /* Container */
    --container-padding: var(--space-5); /* 20px */
    --container-max-width: 480px;

    /* Section */
    --section-gap: var(--space-8); /* 32px */
    --section-padding: var(--space-6); /* 24px */

    /* Card */
    --card-padding: var(--space-4); /* 16px */
    --card-gap: var(--space-3); /* 12px */

    /* Grid */
    --grid-gap: var(--space-3); /* 12px */
    --grid-gap-lg: var(--space-4); /* 16px */
}
```

---

## 🧩 Components

### 1. Buttons

#### Primary Button

```css
.btn-primary {
    padding: var(--space-3) var(--space-6);
    background: linear-gradient(135deg, var(--primary-600), var(--primary-500));
    color: var(--text-inverse);
    border: none;
    border-radius: var(--radius-lg);
    font-size: var(--text-base);
    font-weight: var(--font-semibold);
    box-shadow: var(--shadow-primary);
    transition: all 250ms ease;
    cursor: pointer;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.btn-primary:active {
    transform: scale(0.95);
}
```

#### Secondary Button

```css
.btn-secondary {
    padding: var(--space-3) var(--space-6);
    background: transparent;
    color: var(--primary-600);
    border: 2px solid var(--primary-600);
    border-radius: var(--radius-lg);
    font-size: var(--text-base);
    font-weight: var(--font-semibold);
    transition: all 250ms ease;
}

.btn-secondary:hover {
    background: var(--primary-50);
}
```

#### Icon Button

```css
.btn-icon {
    width: 44px;
    height: 44px;
    padding: 0;
    background: var(--bg-primary);
    border: 1px solid var(--border-light);
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: var(--shadow-sm);
    transition: all 250ms ease;
}

.btn-icon:active {
    transform: scale(0.9);
}
```

#### Add Button (FAB)

```css
.btn-add {
    width: 40px;
    height: 40px;
    padding: 0;
    background: linear-gradient(135deg, var(--primary-600), var(--primary-500));
    color: white;
    border: none;
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: var(--shadow-primary);
    font-size: var(--text-xl);
    font-weight: var(--font-bold);
}
```

### 2. Cards

#### Product/Schedule Card

```css
.card {
    background: var(--bg-primary);
    border-radius: var(--radius-xl);
    padding: var(--space-3);
    box-shadow: var(--shadow-md);
    transition: all 250ms ease;
}

.card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-xl);
}

.card-image {
    width: 100%;
    aspect-ratio: 1;
    border-radius: var(--radius-lg);
    object-fit: cover;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--space-2);
}

.card-body {
    padding: var(--space-2) 0;
}

.card-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: var(--space-3);
}
```

#### Category Card

```css
.category-card {
    background: var(--bg-primary);
    border-radius: var(--radius-lg);
    padding: var(--space-4);
    text-align: center;
    box-shadow: var(--shadow-sm);
    min-width: 100px;
    transition: all 250ms ease;
}

.category-card:active {
    transform: scale(0.95);
}

.category-icon {
    width: 48px;
    height: 48px;
    margin: 0 auto var(--space-2);
    background: linear-gradient(135deg, var(--primary-500), var(--primary-400));
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: var(--text-2xl);
}

.category-name {
    font-size: var(--text-sm);
    font-weight: var(--font-semibold);
    color: var(--text-primary);
    margin-bottom: var(--space-1);
}

.category-count {
    font-size: var(--text-xs);
    color: var(--text-tertiary);
}
```

### 3. Badges

```css
.badge {
    display: inline-flex;
    align-items: center;
    padding: var(--space-1) var(--space-3);
    border-radius: var(--radius-full);
    font-size: var(--text-xs);
    font-weight: var(--font-semibold);
}

.badge-success {
    background: var(--secondary-100);
    color: var(--secondary-700);
}

.badge-warning {
    background: var(--accent-100);
    color: var(--accent-700);
}

.badge-danger {
    background: #fee2e2;
    color: #991b1b;
}

.badge-info {
    background: var(--primary-100);
    color: var(--primary-700);
}
```

### 4. Bottom Navigation

```css
.bottom-nav {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: var(--bg-primary);
    box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.08);
    padding: var(--space-2) 0;
    padding-bottom: calc(var(--space-2) + env(safe-area-inset-bottom));
    z-index: 1000;
}

.bottom-nav-list {
    display: flex;
    justify-content: space-around;
    align-items: center;
}

.bottom-nav-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: var(--space-1);
    padding: var(--space-2);
    min-width: 60px;
    color: var(--text-tertiary);
    text-decoration: none;
    transition: all 250ms ease;
}

.bottom-nav-item.active {
    color: var(--primary-600);
}

.bottom-nav-icon {
    font-size: var(--text-2xl);
    position: relative;
}

.bottom-nav-item.active .bottom-nav-icon {
    background: var(--primary-100);
    padding: var(--space-2);
    border-radius: var(--radius-full);
}

.bottom-nav-label {
    font-size: var(--text-xs);
    font-weight: var(--font-medium);
}

.bottom-nav-item.active .bottom-nav-label {
    font-weight: var(--font-semibold);
}
```

---

## 🎭 Border Radius

```css
:root {
    --radius-none: 0;
    --radius-sm: 0.5rem; /* 8px */
    --radius-md: 0.75rem; /* 12px */
    --radius-lg: 1rem; /* 16px */
    --radius-xl: 1.25rem; /* 20px */
    --radius-2xl: 1.5rem; /* 24px */
    --radius-full: 9999px; /* Full circle */
}
```

---

## ✨ Animations

### Transition Durations

```css
:root {
    --duration-fast: 150ms;
    --duration-base: 250ms;
    --duration-slow: 350ms;
    --duration-slower: 500ms;
}
```

### Easing Functions

```css
:root {
    --ease-in: cubic-bezier(0.4, 0, 1, 1);
    --ease-out: cubic-bezier(0, 0, 0.2, 1);
    --ease-in-out: cubic-bezier(0.4, 0, 0.2, 1);
    --ease-bounce: cubic-bezier(0.68, -0.55, 0.265, 1.55);
}
```

### Common Animations

```css
/* Fade In */
@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

/* Slide Up */
@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Scale In */
@keyframes scaleIn {
    from {
        opacity: 0;
        transform: scale(0.9);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

/* Shimmer Loading */
@keyframes shimmer {
    0% {
        background-position: -200% 0;
    }
    100% {
        background-position: 200% 0;
    }
}

.shimmer {
    background: linear-gradient(
        90deg,
        var(--bg-secondary) 25%,
        var(--bg-tertiary) 50%,
        var(--bg-secondary) 75%
    );
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
}
```

---

## 📱 Responsive Design

### Breakpoints

```css
/* Mobile (Default) */
@media (min-width: 320px) {
}

/* Mobile Large */
@media (min-width: 375px) {
}

/* Mobile XL */
@media (min-width: 414px) {
}

/* Tablet */
@media (min-width: 768px) {
}

/* Desktop */
@media (min-width: 1024px) {
}
```

---

## 🚀 Implementation Guide

### 1. Setup CSS Variables

Tambahkan semua CSS variables di file utama:

```html
<link rel="stylesheet" href="/assets/css/variables.css" />
```

### 2. Import Component Styles

```html
<link rel="stylesheet" href="/assets/css/components.css" />
```

### 3. Page Structure

```html
<div class="page-container">
    <header class="page-header">
        <!-- Header content -->
    </header>

    <main class="page-content">
        <!-- Main content sections -->
    </main>

    <nav class="bottom-nav">
        <!-- Navigation items -->
    </nav>
</div>
```

### 4. Utilities

Gunakan utility classes untuk spacing, colors, dll:

```html
<div class="mt-4 px-5 bg-primary-50">
    <!-- Content -->
</div>
```

---

## 📦 File Structure

```
resources/
├── css/
│   ├── variables.css         # CSS Variables
│   ├── components.css        # Component styles
│   ├── utilities.css         # Utility classes
│   └── app.css              # Main styles
├── js/
│   ├── components/          # JS components
│   │   ├── bottom-nav.js
│   │   ├── cards.js
│   │   └── modals.js
│   └── app.js              # Main JS
└── views/
    └── components/          # Blade components
        ├── card.blade.php
        ├── badge.blade.php
        └── button.blade.php
```

---

**Version:** 1.0.0  
**Last Updated:** November 19, 2025  
**Author:** SIAG NEKAS Development Team
