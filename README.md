# AI-Based Agriculture Portal for Crop Recommendation, Yield Prediction and Direct Farmer-to-Customer Trade

[![Project Phase](https://img.shields.io/badge/Project-Phase--I%20(2026--2027)-brightgreen.svg)](#)
[![Department](https://img.shields.io/badge/Department-Computer%20Science%20%26%20Engineering-blue.svg)](#)
[![Institution](https://img.shields.io/badge/College-MGM's%20College%20of%20Engineering%2C%20Nanded-orange.svg)](#)
[![Stack](https://img.shields.io/badge/Stack-PHP%20%7C%20MySQL%20%7C%20Python%20%7C%20ML-yellow.svg)](#)

---

## 👥 Project Team & Guide

| Role | Name | Roll Number | Department |
| :--- | :--- | :--- | :--- |
| **Team Member** | Dhananjay Kadam | 133 | Computer Science & Engineering |
| **Team Member** | Bramanand Gaikwad | **165** | Computer Science & Engineering |
| **Team Member** | Prajwal Govande | **126** | Computer Science & Engineering |
| **Team Member** | Kasralikar Murdula | **121** | Computer Science & Engineering |
| **Project Guide** | **Mr. Syed Ateeq B.** | Assistant Professor | Computer Science & Engineering |

**Institution:** Department of Computer Science & Engineering, Mahatma Gandhi Mission's (MGM's) College of Engineering, Nanded, Maharashtra, India.  
**Academic Year:** 2026 – 2027  
**Curriculum Track:** Final Year Project – Phase I  

---

## 📑 Table of Contents
- [Project Overview](#-project-overview)
- [Key Objectives](#-key-objectives)
- [System Architecture](#-system-architecture)
- [Core Features & Modules](#-core-features--modules)
  - [1. Machine Learning & Predictive Analytics](#1-machine-learning--predictive-analytics)
  - [2. Farmer-to-Customer (F2C) E-Commerce & Marketplace](#2-farmer-to-customer-f2c-e-commerce--marketplace)
  - [3. Intelligent Value-Added Services](#3-intelligent-value-added-services)
- [Technologies & Libraries Used](#-technologies--libraries-used)
- [Database Schema](#-database-schema)
- [Repository Structure](#-repository-structure)
- [Documentation & Reports](#-documentation--reports)
- [Installation & Setup Guide](#-installation--setup-guide)
- [License & Acknowledgements](#-license--acknowledgements)

---

## 🌾 Project Overview

Farmers in traditional agricultural ecosystems often make critical cultivation and fertilization decisions based on intuition or anecdotal practices, without access to timely, data-driven intelligence. Concurrently, small and marginal agricultural producers depend heavily on multi-tiered traditional supply chains involving commission agents, brokers, and wholesalers. This structure inflates consumer food prices while diminishing farmer earnings and eliminating price transparency.

The **AI-Based Agriculture Portal** resolves these dual challenges by unifying **advanced machine learning models** and a **direct Farmer-to-Customer (F2C) trade portal** into a single, cohesive web platform:
1. **Intelligent Cultivation Advisory:** Delivers scientific recommendations for crop selection, fertilizer formulation, historical rainfall insights, and harvest yield predictions based on regional soil chemistry and climatic parameters.
2. **Direct Market Disintermediation:** Empowers farmers to list crops directly to consumers at fair prices, complete transactions with online payment gateways, and manage inventory and order lifecycles transparently.
3. **Real-Time Farmer Support:** Integrated with a conversational AI chatbot, hyper-local 5-day weather forecasts, agricultural news feeds, bilingual support (English and Hindi), and email OTP verification.

---

## 🎯 Key Objectives

1. **Role-Based Access Management:** Secure, role-segregated portals for Farmers, Customers, and Administrators.
2. **Crop & Fertilizer Intelligence:**
   - Accurate crop recommendation matching soil N-P-K nutrients, temperature, humidity, pH, and rainfall.
   - Precise fertilizer recommendations matching soil conditions and target crop types.
3. **Agrometeorological & Yield Forecasting:**
   - Multi-year rainfall estimations across Indian subdivisions and seasons.
   - Area-based crop production and yield estimations in quintals.
4. **Direct Agricultural Trade:**
   - Elimination of intermediaries via a transparent digital trading marketplace with live shopping cart operations.
   - Secure digital payments (Stripe API / demo checkout mode).
5. **Farmer Accessibility:**
   - Multi-lingual user interface (English & Hindi).
   - Conversational AI advisory chatbot for natural language agricultural queries.

---

## 🏗️ System Architecture

The platform follows a robust multi-tier architectural pattern:

```
+-----------------------------------------------------------------------------------+
|                            PRESENTATION LAYER                                      |
|   Bootstrap 4, HTML5, CSS3, JavaScript, jQuery, DataTables, i18n (English/Hindi)  |
+-----------------------------------------------------------------------------------+
                                         │
                                         ▼
+-----------------------------------------------------------------------------------+
|                            APPLICATION LAYER (PHP 7.4+ / Apache)                  |
|   • Session & Auth Manager (bcrypt, OTP)   • Cart & Order Fulfillment Engine      |
|   • Payment Processing (Stripe API)        • Python ML Execution Subsystem         |
+-----------------------------------------------------------------------------------+
                         │                                    │
                         ▼                                    ▼
+------------------------------------+   +------------------------------------------+
|          DATA LAYER                |   |          EXTERNAL SERVICES               |
|   MySQL / MariaDB                  |   |   • OpenAI API (ChatGPT & daily quotes)  |
|   • farmerlogin   • custlogin      |   |   • OpenWeatherMap API (5-day forecast)  |
|   • farmer_crops_trade • cart      |   |   • NewsAPI (Agricultural headlines)     |
|   • farmer_history • state/district|   |   • Gmail SMTP / PHPMailer (OTP / alerts)|
+------------------------------------+   +------------------------------------------+
```

---

## 🚀 Core Features & Modules

### 1. Machine Learning & Predictive Analytics
- **Crop Recommendation (`recommend.py`):**
  - Algorithm: Random Forest Classifier
  - Inputs: Nitrogen (N), Phosphorus (P), Potassium (K), Temperature, Humidity, Soil pH, Rainfall
  - Output: Scientifically recommended crop optimal for the local agronomic conditions.
- **Crop Prediction (`ZDecision_Tree_Model_Call.py`):**
  - Algorithm: Decision Tree Classifier & Regional Frequency Matching
  - Inputs: State Name, District Name, Season (Kharif, Rabi, Summer, Whole Year)
  - Output: Best matched traditional crops for the region.
- **Fertilizer Recommendation (`fertilizer_recommendation.py`):**
  - Algorithm: Decision Tree Classifier
  - Inputs: Temperature, Humidity, Soil Moisture, Soil Type, Crop Type, Nitrogen, Potassium, Phosphorus
  - Output: Recommended fertilizer formulation (e.g., Urea, DAP, 14-35-14, 28-28-0).
- **Rainfall Prediction (`rainfall_prediction.py`):**
  - Dataset: Historical IMD rainfall records across Indian meteorological subdivisions (1901–2015).
  - Inputs: Subdivision, Month
  - Output: Historical average precipitation metrics in millimeters.
- **Yield Prediction (`yield_prediction.py`):**
  - Algorithm: Random Forest Regressor with One-Hot Categorical Encoding
  - Inputs: State, District, Season, Crop Name, Cultivation Area (in Hectares)
  - Output: Projected crop production in quintals.

### 2. Farmer-to-Customer (F2C) E-Commerce & Marketplace
- **Crop Listing & Inventory Management:** Farmers list harvested crops with category, quantity (kg), and price per kg.
- **Dynamic Customer Cart:** Customers browse fresh produce, view stock availability, add items to cart, and modify quantities.
- **Order Processing & Checkout:** Atomic database updates decrements seller inventory upon successful order completion.
- **Payment Gateway Integration:** Integrated with Stripe API for secure credit/debit card processing, alongside a configurable offline/demo mode.
- **Trade History & Status Tracking:** Complete order tracking across lifecycle stages (*Packed*, *Shipped*, *Delivered*).

### 3. Intelligent Value-Added Services
- **AI Chatbot (`fchatgpt.php`):** Interactive farmer assistant powered by OpenAI API for query resolution on pests, crop diseases, and best practices.
- **Weather Forecast (`fweather_forecast.php`):** 5-day hyper-local meteorological forecasts (Temperature, Humidity, Wind speed, Precipitation) via OpenWeatherMap API.
- **Agricultural News (`fnewsfeed.php`):** Real-time aggregation of national and international farming news using NewsAPI.
- **Multilingual Localization:** Instant toggling between English and Hindi (`assets/lang/en.json`, `assets/lang/hi.json`) via `i18n.js`.
- **Two-Factor Authentication:** Email OTP delivery for registration and password recovery using PHPMailer over SSL SMTP.

---

## 🛠️ Technologies & Libraries Used

### Front-End:
- **HTML5 & CSS3**
- **Bootstrap 4** (Responsive design & Argon/CreativeTim theme components)
- **JavaScript (ES6+) & jQuery**
- **DataTables** (Dynamic client-side sorting, pagination, and searching)
- **FontAwesome 5 & Nucleo Icons**

### Back-End:
- **PHP 7.4+ / 8.x** (Apache Server environment via XAMPP)
- **PHPMailer** (SMTP mail delivery)
- **Stripe PHP SDK** (Payment processing)

### Machine Learning & Data Science:
- **Python 3.8+ / 3.10+**
- **Scikit-learn** (RandomForestClassifier, RandomForestRegressor, DecisionTreeClassifier, OneHotEncoder)
- **Pandas & NumPy** (Dataset manipulation and matrix transformations)
- **Joblib** (Model serialization)

### Database:
- **MySQL / MariaDB** (Relational storage with foreign key integrity and UTF-8 encoding)

---

## 🗄️ Database Schema

The database (`db/agriculture_portal.sql`) includes the following core relations:

| Table | Description |
| :--- | :--- |
| `admin` | Administrator authentication credentials and account settings |
| `farmerlogin` | Farmer credentials, contact details, district, and state |
| `custlogin` | Customer accounts, addresses, phone numbers, and profile data |
| `crops` | Catalog of crops with baseline attributes |
| `farmer_crops_trade` | Active crop listings published by farmers with stock quantities and unit prices |
| `cart` | Session-managed shopping cart entries for active buyers |
| `farmer_history` | Historical logs of completed transactions and order details |
| `production_approx` | Agricultural production reference statistics |
| `state` & `district` | Normalized master lists of Indian states and corresponding districts |
| `contactus` | Customer and visitor inquiries and support tickets |

---

## 📁 Repository Structure

```
Project-Phase-I-2026-27/
│
├── admin/                         # Administrator control panel and management scripts
│   ├── alogin.php                 # Admin login page
│   ├── afarmers.php               # Farmer management & verification
│   ├── acustomers.php             # Customer management
│   ├── aproducedcrop.php          # Platform-wide crop inventory oversight
│   └── aviewmsg.php               # Contact inquiries feedback viewer
│
├── assets/                        # Static UI assets
│   ├── css/                       # Stylesheets (Bootstrap, Argon theme, custom CSS)
│   ├── js/                        # JavaScript helpers and i18n localization engine
│   ├── lang/                      # Translation files (en.json, hi.json)
│   └── img/                       # Logos, icons, and theme illustrations
│
├── customer/                      # Customer portal and e-commerce subsystem
│   ├── clogin.php                 # Customer login and session authentication
│   ├── cregister.php              # Customer registration with validation
│   ├── cbuy_crops.php             # Marketplace catalogue of available farmer crops
│   ├── ccheckout.php              # Order summary and checkout initiation
│   ├── cart_lib.php               # Shopping cart business logic
│   ├── order_lib.php              # Order fulfillment and payment ledger logic
│   └── StripePayment/             # Stripe API integration and webhook listeners
│
├── db/                            # Database definitions and SQL dumps
│   └── agriculture_portal.sql     # Complete database schema and initial seed data
│
├── docs/                          # Academic documentation, research paper and reports
│   ├── Agriculture_Portal_Research_Paper.pdf  # Published/draft academic research paper
│   ├── Project_Report_Phase_I.pdf             # Comprehensive 59-page Final Year Project Report
│   ├── Vadapav final year.pdf                 # Original project documentation report
│   └── WhatsApp Image 2026-11-19 at 10.37.35 PM.jpeg # Project poster presentation template
│
├── farmer/                        # Farmer portal, ML predictors and agricultural tools
│   ├── flogin.php                 # Farmer login
│   ├── fregister.php              # Farmer registration with state/district selection
│   ├── ftradecrops.php            # Sell crops and add new harvest stock
│   ├── fselling_history.php       # Past trade and sales ledger
│   ├── fcrop_recommendation.php   # UI for soil-nutrient crop recommendation
│   ├── fcrop_prediction.php       # UI for regional crop prediction
│   ├── ffertilizer_recommendation.php # UI for fertilizer recommendations
│   ├── frainfall_prediction.php   # UI for meteorological rainfall forecast
│   ├── fyield_prediction.php      # UI for harvest production calculation
│   ├── fweather_forecast.php      # Live 5-day weather forecast page
│   ├── fnewsfeed.php              # Real-time agriculture news feed
│   ├── fchatgpt.php               # OpenAI-powered conversational assistant
│   ├── ml_runner.php              # Safe PHP-to-Python execution wrapper
│   ├── requirements.txt           # Python dependency specification for ML models
│   └── ML/                        # Python machine learning models and training datasets
│       ├── crop_prediction/       # Decision tree model & crop dataset
│       ├── crop_recommendation/   # Random Forest model & soil dataset
│       ├── fertilizer_recommendation/ # Fertilizer Decision Tree & dataset
│       ├── rainfall_prediction/   # Rainfall historical dataset (1901-2015)
│       └── yield_prediction/      # Random Forest regressor & Karnataka crop data
│
├── lib/                           # Core utilities and shared helpers
│   ├── config.php                 # Environment variable loader (.env reader)
│   └── mailer.php                 # PHPMailer / SMTP wrapper
│
├── smtp/                          # PHPMailer vendor library for email dispatch
│
├── .env.example                   # Template environment configuration file
├── .gitignore                     # Git exclusion rules
├── .htaccess                      # Apache rewrite and security rules
├── contact.php                    # Contact Us and feedback form
├── footer.php                     # Shared website footer
├── index.php                      # Main public landing page
├── openai.php                     # OpenAI API server proxy endpoint
├── sql.php                        # Centralized MySQL database connection script
├── requirements.txt               # Project-wide Python dependencies
└── README.md                      # Comprehensive project documentation (this file)
```

---

## 📚 Documentation & Reports

All academic documentation and research materials for Project Phase-I are provided in the [`docs/`](docs/) directory:

1. **Research Paper:**
   - **File:** [`docs/Agriculture_Portal_Research_Paper.pdf`](docs/Agriculture_Portal_Research_Paper.pdf)
   - **Title:** *An AI-Based Agriculture Portal for Crop Recommendation, Yield Prediction and Direct Farmer-to-Customer Trade*
   - **Authors:** Kasralikar Mrudula, Bramhanand Gaikwad, Prajwal Govande, Dhananjay Kadam
   - **Guide:** Mr. Syed Ateeq B.
   - **Department:** Computer Science & Engineering, MGM's College of Engineering, Nanded

2. **Phase-I Project Report:**
   - **File:** [`docs/Project_Report_Phase_I.pdf`](docs/Project_Report_Phase_I.pdf) *(also available as `Vadapav final year.pdf`)*
   - **Description:** Complete 59-page project report detailing system requirements, SRS, database design, high-level architecture, module implementation, testing methodologies, and performance evaluations.

3. **Presentation & Poster Asset:**
   - **File:** [`docs/WhatsApp Image 2026-11-19 at 10.37.35 PM.jpeg`](docs/WhatsApp%20Image%202026-11-19%20at%2010.37.35%20PM.jpeg)
   - **Description:** Presentation poster reference and architectural workflow design.

---

## ⚙️ Installation & Setup Guide

### 1. Prerequisites
- **Web Server:** [XAMPP](https://www.apachefriends.org/) (Apache 2.4+ and MariaDB/MySQL 10.4+)
- **PHP:** Version 7.4 or higher (cURL and OpenSSL extensions enabled)
- **Python:** Python 3.8+ (with `pip` configured in system PATH)
- **Web Browser:** Modern web browser (Chrome, Edge, Firefox)

---

### 2. Clone the Repository
Clone the repository into your local server root (for XAMPP, into `C:\xampp\htdocs\`):
```bash
cd C:\xampp\htdocs
git clone https://github.com/s23kadamdhananjay-debug/Project-Phase-I-2026-27.git agbramha
```

---

### 3. Install Python Dependencies
Install all required machine learning and data processing libraries:
```bash
cd C:\xampp\htdocs\agbramha
pip install -r requirements.txt
```

---

### 4. Database Configuration
1. Open the **XAMPP Control Panel** and start both **Apache** and **MySQL**.
2. Open your browser and navigate to `http://localhost/phpmyadmin/`.
3. Create a new database named `agriculture_portal`:
   ```sql
   CREATE DATABASE agriculture_portal CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
   ```
4. Click on **Import**, choose the file `db/agriculture_portal.sql` from the project repository, and click **Go**.

---

### 5. Environment Configuration
1. In the project root, copy `.env.example` to create a `.env` file:
   ```bash
   cp .env.example .env
   # Or on Windows PowerShell:
   Copy-Item .env.example .env
   ```
2. Open `.env` in an editor and configure your credentials:
   ```ini
   APP_TIMEZONE=Asia/Kolkata
   APP_URL=http://localhost/agbramha

   # Database settings
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_NAME=agriculture_portal
   DB_USER=root
   DB_PASSWORD=

   # External API Keys (Optional / As needed)
   OPENAI_API_KEY=your_openai_api_key_here
   OPENAI_MODEL=gpt-4o-mini
   OPENWEATHER_API_KEY=your_openweather_api_key_here
   NEWS_API_KEY=your_news_api_key_here

   # Stripe payments (Leave blank and STRIPE_DEMO_MODE=1 for offline testing)
   STRIPE_SECRET_KEY=
   STRIPE_PUBLISHABLE_KEY=
   STRIPE_DEMO_MODE=1

   # SMTP Email Settings for OTP
   SMTP_HOST=smtp.gmail.com
   SMTP_PORT=465
   SMTP_SECURE=ssl
   SMTP_USERNAME=your_email@gmail.com
   SMTP_PASSWORD=your_gmail_app_password
   SMTP_FROM=your_email@gmail.com
   SMTP_FROM_NAME=Agriculture Portal

   # Python command
   PYTHON=python
   ```

---

### 6. Launch the Application
1. In your web browser, open:
   ```
   http://localhost/agbramha/
   ```
2. **Default Logins for Testing:**
   - **Farmer Portal:** Register a new farmer account or use existing farmer credentials from the database.
   - **Customer Portal:** Register as a customer to browse and purchase crops.
   - **Admin Portal:** Accessible at `http://localhost/agbramha/admin/alogin.php`.

---

## 📜 License & Acknowledgements

- **Institution:** Department of Computer Science & Engineering, MGM's College of Engineering, Nanded.
- **Project Guide:** Mr. Syed Ateeq B.
- **License:** Licensed under the [MIT License](https://opensource.org/licenses/MIT).
