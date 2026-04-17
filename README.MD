# Ardetho ERP

Ardetho ERP is a modular and customizable enterprise resource planning system developed as an academic front-end project.  
The proposal is to offer a modern, organized and scalable environment for managing core business areas such as clients, products, sales, finance, reports, settings, profile customization, and human resources.

## Overview

The system was designed with a modular concept, allowing the environment to be adapted according to the company's profile and operational needs.  
It includes both a public presentation area and an internal authenticated ERP area.

## Main Features

- Authentication flow with login and protected internal pages
- Dashboard with dynamic cards, notifications, summary and financial visualization
- Clients module with PF/PJ registration structure
- Products and services module
- Sales module
- Financial module with income and expense structure
- Reports module connected to internal data
- ERP modules page with activation/deactivation of features
- Settings page with visual and dashboard customization
- Profile page with company branding customization
- Human Resources (HR) module with employee registration, filters and editing
- Public pages:
  - Home
  - About
  - Modules
  - Contact

## Technologies Used

- HTML5
- CSS3
- JavaScript (Vanilla JS)
- LocalStorage for persistence
- Google Fonts (Inter)

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
└── assets/
    ├── css/
    ├── js/
    └── images/