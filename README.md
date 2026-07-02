# 🌍 World Recipes — Global Culinary Discovery Platform

![Symfony](https://img.shields.io/badge/Symfony-7.2-black?logo=symfony)
![PHP](https://img.shields.io/badge/PHP-8.2-blue?logo=php)
![MySQL](https://img.shields.io/badge/MySQL-8.0-orange?logo=mysql)
![Doctrine](https://img.shields.io/badge/Doctrine-ORM-green)
![License](https://img.shields.io/badge/License-MIT-yellow)

A full-featured **Symfony 7.2** web application for discovering and exploring authentic recipes from around the world. Built with modern PHP 8.2 attributes, Doctrine ORM, and a custom Bootstrap 5 UI.

Public visitors can browse recipes by country, search by ingredients, filter by difficulty and calories, and view detailed recipe pages with nutrition breakdowns. Administrators have a complete CRUD dashboard for managing all content.

---

## ✨ Features

### 🌐 Public Features
| Feature | Description |
|---------|-------------|
| 🏠 **Homepage** | Featured countries with recipe stats, global cuisine cards |
| 🔍 **Explore** | Browse 23+ countries, filter by region (Africa, Asia, Europe, Americas, etc.) |
| 🍳 **Recipe Discovery** | Search recipes by name, filter by country, difficulty, max calories |
| 📄 **Recipe Details** | Full recipe view with ingredients, instructions, nutrition info, print-friendly layout |
| 🔢 **Nutrition Tracking** | Dynamic calorie calculation per recipe and per serving |
| 🔐 **Authentication** | User registration and login with Symfony Security |

### 🛡️ Admin Features (`/admin/*`)
| Feature | Description |
|---------|-------------|
| 📊 **Dashboard** | Overview with stats (countries, recipes, users) |
| 📝 **Recipe CRUD** | Create, edit, delete recipes with image upload |
| 🌍 **Country CRUD** | Manage countries with cuisine descriptions |
| 🧂 **Ingredient CRUD** | Manage ingredients with calorie data |
| 👤 **User CRUD** | Manage registered users |
| 📋 **Menu CRUD** | Create curated menus with recipe collections |

---

## 🖼️ Screenshots

### 🏠 Homepage
![Homepage](screenshots/Home1.png)
*Hero section with featured cuisines and stats*

![Homepage Countries](screenshots/Home2.png)
*Global culinary adventures carousel*

![Homepage Cards](screenshots/Home3.png)
*Country cuisine cards with recipe counts and avg calories*

---

### 🔍 Explore Page
![Explore Hero](screenshots/Explore1.png)
*Global discovery with search and region stats*

![Explore Filters](screenshots/Explore2.png)
*Filter by region and sort options*

![Explore Regions](screenshots/Explore3.png)
*Cuisines by region with country counts*

![Explore Country Cards](screenshots/Explore4.png)
*Detailed country cards with popular recipes*

---

### 🍳 Recipes Page
![Recipes Quick & Easy](screenshots/Recipes1.png)
*Quick & easy recipes under 30 minutes*

![Recipes Low Calorie](screenshots/Recipes2.png)
*Low calorie favorites under 400 kcal*

---

### 📄 Recipe Details
![Recipe Header](screenshots/RecipesDetails1.png)
*Recipe hero with image, title, and key stats*

![Recipe Ingredients](screenshots/RecipesDetails2.png)
*Ingredients list with weights and calorie breakdown*

![Recipe Nutrition](screenshots/RecipesDetails3.png)
*Nutrition information, difficulty, country, and action buttons*

![Recipe PDF Print](screenshots/RecipesDetailsPDF.png)
*Print-friendly layout for PDF generation*

---

### 🔐 Authentication
![Sign In](screenshots/Siginin.png)
*Login page with welcome message and feature highlights*

![Sign Up](screenshots/Siginup.png)
*Registration with feature benefits sidebar*

---

### 🔐 Admin Dashboard
![Dashboard Home1](screenshots/AdminDashboard1.png)
![Dashboard Home2](screenshots/AdminDashboard2.png)
*Admin home dashboard page*

![Dashboard Users ](screenshots/AdminDashboard3.png)
*Admin Dashboard Crud Users*

![Dashboard Countries ](screenshots/AdminDashboard4.png)
*Admin Dashboard Crud Countries*

![Dashboard Recipes ](screenshots/AdminDashboard5.png)
*Admin Dashboard Crud Recipes*

![Dashboard Ingredients ](screenshots/AdminDashboard6.png)
*Admin Dashboard Crud Ingredients*

![Dashboard Add Ingredients ](screenshots/AdminDashboard7.png)
*Admin Dashboard Ingredients Form*

---
## 🚀 Quick Start

### Prerequisites
- PHP 8.2+
- Composer
- MySQL 8.0+
- Symfony CLI (optional but recommended)

### 1. Clone & Install
```bash
git clone https://github.com/yourusername/world-recipes.git
cd world-recipes
composer install
```

### 2. Configure Environment
```bash
# Copy environment file
cp .env .env.local

# Edit .env.local with your database credentials
DATABASE_URL="mysql://user:password@127.0.0.1:3306/world_recipes?serverVersion=8.0"
```

### 3. Setup Database
```bash
# Create database
php bin/console doctrine:database:create

# Run migrations
php bin/console doctrine:migrations:migrate

# (Optional) Load demo data
php bin/console doctrine:fixtures:load
```

### 4. Create Admin User
```bash
php bin/console security:hash-password
# Then create a user via registration and promote to admin in DB:
# UPDATE user SET roles = '["ROLE_ADMIN"]' WHERE email = 'your@email.com';
```

### 5. Start Server
```bash
symfony server:start
# OR
php -S 127.0.0.1:8000 -t public/
```

Visit: `http://127.0.0.1:8000/`

---

## 🗄️ Database Schema

```
User (1) ───────< (N) Recipe
Country (1) ────< (N) Recipe
Recipe (1) ─────< (N) RecipeIngredient >──── (N) Ingredient
User (1) ───────< (N) Menu >──────< (N) Recipe
User (1) ───────< (1) NutritionProfile
```

**Key Relationships:**
- **Recipe ↔ Ingredient**: Many-to-Many via `RecipeIngredient` join table (with quantity, unit, calories)
- **Recipe ↔ Country**: Many-to-One
- **Menu ↔ Recipe**: Many-to-Many via join table
- **User ↔ Recipe**: Many-to-One (ownership)

---

## 📁 Project Structure

```
world-recipes/
├── config/              # Symfony configuration
│   ├── packages/        # Doctrine, Security, Twig, etc.
│   └── routes.yaml      # Route definitions
├── src/
│   ├── Controller/      # Home, Explore, Recipe, Security, Admin
│   ├── Entity/            # User, Recipe, Country, Ingredient, Menu, etc.
│   ├── Repository/        # Custom DQL queries
│   ├── Form/              # Registration form
│   └── Service/           # FileUploader
├── templates/             # Twig templates
│   ├── home/
│   ├── explore/
│   ├── recipes/
│   ├── admin/
│   ├── security/
│   └── includes/          # Navbar, footer
├── public/                # Entry point, assets
├── migrations/            # Doctrine migrations
├── composer.json
└── README.md
```

---

## 🛠️ Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Symfony 7.2, PHP 8.2 |
| ORM | Doctrine 3.x |
| Database | MySQL 8.0 |
| Frontend | Bootstrap 5, HTML5, CSS3, Font Awesome |
| Auth | Symfony Security (password hashing, role-based) |
| Templating | Twig 3.x |
| File Uploads | Symfony String (slugger) + custom FileUploader |

---

## 🛡️ Security

- **Password hashing**: Symfony PasswordHasher (auto)
- **Role-based access**: `ROLE_USER`, `ROLE_ADMIN`
- **CSRF protection**: Enabled on all forms
- **Admin routes**: Protected by `IS_AUTHENTICATED_FULLY` + admin checks
- **File uploads**: Unique filenames, public directory only

---

## 📜 License

This project is open-source under the [MIT License](LICENSE).

---

## 👤 Author

**Zoghlami Hamza** 
🔗 [LinkedIn](https://www.linkedin.com/in/hamza-zoghlami) | 🐙 [GitHub](https://github.com/Hamza-zgh)
