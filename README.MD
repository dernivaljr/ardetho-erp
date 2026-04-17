# Ardetho ERP

![Ardetho ERP Preview](assets/images/preview-dashboard.png)

Ardetho ERP is a modular and customizable enterprise resource planning system developed as an academic front-end project.  
The project was created to simulate a modern ERP environment with public presentation pages and an internal authenticated area, focusing on usability, modularity, branding customization, and interface consistency.

---

## Overview

The main idea of Ardetho ERP is to provide a management platform that can be adapted according to different business profiles.  
The environment was structured to support a modular approach, allowing the system to grow progressively with the company.

The project includes:

- a public presentation area
- an internal ERP area
- module activation and deactivation
- company branding customization
- HR management
- dashboard visualization
- LocalStorage-based persistence

---

## Main Features

### Public pages
- Home page
- About page
- Modules page
- Contact page

### Internal ERP pages
- Login
- Dashboard
- Clients
- Products
- Sales
- Financial
- Reports
- ERP Modules
- Settings
- Profile
- HR
- HR Form

### Functional highlights
- Authentication with protected internal routes
- Multi-user simulation with different company identities
- Company branding customization:
  - company name
  - display name
  - logo
  - favicon
  - primary color
  - accent color
- Dynamic dashboard with:
  - metrics
  - notifications
  - summary
  - activities
  - financial chart
  - operational status
- Clients module with PF/PJ structure
- Products and services module
- Sales module
- Financial module with income and expenses
- Reports connected to internal data
- ERP modules activation/deactivation
- HR module with:
  - employee listing
  - salary field
  - status filter
  - department filter
  - employee form
  - create/edit/delete operations

---

## Technologies Used

- HTML5
- CSS3
- JavaScript (Vanilla JS)
- LocalStorage
- Google Fonts (Inter)

---

## Project Structure

```text
/
├── index.html
├── about.html
├── modules.html
├── contact.html
├── login.html
├── dashboard.html
├── clients.html
├── client-form.html
├── products.html
├── product-form.html
├── sales.html
├── sale-form.html
├── financial.html
├── financial-form.html
├── hr.html
├── hr-form.html
├── reports.html
├── erp-modules.html
├── settings.html
├── profile.html
│
├── assets/
│   ├── css/
│   │   ├── variables.css
│   │   ├── reset.css
│   │   ├── global.css
│   │   ├── layout.css
│   │   ├── components.css
│   │   ├── public.css
│   │   ├── auth.css
│   │   ├── dashboard.css
│   │   ├── pages.css
│   │   └── responsive.css
│   │
│   ├── js/
│   │   ├── data.js
│   │   ├── storage.js
│   │   ├── auth.js
│   │   ├── dashboard.js
│   │   ├── clients.js
│   │   ├── client-form.js
│   │   ├── products.js
│   │   ├── product-form.js
│   │   ├── sales.js
│   │   ├── sale-form.js
│   │   ├── financial.js
│   │   ├── financial-form.js
│   │   ├── reports.js
│   │   ├── modules.js
│   │   ├── settings.js
│   │   ├── profile.js
│   │   ├── hr.js
│   │   └── hr-form.js
│   │
│   └── images/
│       ├── ardetho-logo.png
│       ├── ardetho-icon.png
│       ├── preview-dashboard.png
│       ├── mecanica-xyz-logo.png
│       └── mecanica-xyz-icon.png
│
├── README.md
├── LICENSE
└── .gitignore