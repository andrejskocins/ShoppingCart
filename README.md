# Laravel Shopping Cart

## OVERVIEW
--------
This project is a shopping cart web application built with Laravel. It provides core e-commerce functionality, allowing users to browse products and manage a persistent shopping cart. The system supports different experiences for guest and authenticated users, including a seamless cart merge process upon login.

## FEATURES
--------
- **User Authentication**: Standard register, login, and logout functionality.
- **Product Catalog**: A clean, grid-based interface for browsing products.
- **Dual Cart System**:
    - **Guest Cart**: Visitors can add items to a temporary cart stored in a browser cookie.
    - **Authenticated Cart**: Registered users have their cart contents saved to their account in the database.
- **Automatic Cart Merging**: When a guest with items in their cart logs in, their guest cart is automatically merged with their user cart.
- **Stock Validation**: Item quantities are validated against available product stock during cart operations.
- **Comprehensive Test Suite**: Includes feature tests to ensure the reliability of the core cart functionality.

## TECHNOLOGIES
------------
- PHP / Laravel Framework
- Blade Templating Engine
- Tailwind CSS
- SQLite (for development)

## PROJECT STRUCTURE
-----------------
```
shopping_cart/
├── app/
│   ├── Http/Controllers/  # Application controllers
│   │   ├── CartController.php
│   │   └── ProductController.php
│   ├── Listeners/         # Event listeners
│   │   └── MergeGuestCartAfterLogin.php
│   ├── Models/            # Eloquent database models
│   │   ├── Cart.php
│   │   ├── CartItem.php
│   │   ├── Product.php
│   │   └── User.php
│   └── Services/          # Business logic services
│       ├── CartMergeService.php
│       ├── CartService.php
│       ├── GuestCartRepository.php
│       └── UserCartRepository.php
├── database/
│   ├── migrations/        # Database schema migrations
│   └── seeders/           # Database seeders
├── resources/
│   └── views/             # Blade templates
├── routes/
│   └── web.php            # Web URL routing
└── tests/
    └── Feature/           # Feature tests
        └── Cart/
            └── CartFlowTest.php
```

## DATABASE MODELS
---------------
- **User**: Stores user account information (name, email, password). Provided by Laravel Breeze.
- **Product**: Stores product details, including `name`, `description`, `price_cents`, and `stock`.
- **Cart**: Represents a shopping cart, linked to a `User` via a `user_id`.
- **CartItem**: A pivot model representing a `Product` within a `Cart`, storing the `quantity`.

## INSTALLATION
------------
1.  **Clone the repository:**
    ```bash
    git clone <repository-url>
    cd shopping_cart
    ```

2.  **Install dependencies:**
    ```bash
    composer install
    npm install
    ```

3.  **Set up your environment:**
    *   Copy the `.env.example` file to a new file named `.env`.
    *   Update the `DB_*` variables if you are not using the default `database.sqlite`.
    ```bash
    cp .env.example .env
    touch database/database.sqlite
    ```

4.  **Generate an application key:**
    ```bash
    php artisan key:generate
    ```

5.  **Run database migrations and seeders:**
    ```bash
    php artisan migrate --seed
    ```

6.  **Build frontend assets:**
    ```bash
    npm run dev
    ```

## USAGE
-----
1.  **Start the development server:**
    ```bash
    php artisan serve
    ```
2.  Navigate to `http://127.0.0.1:8000`.
3.  Browse products and add them to the cart as a guest.
4.  Register a new account or log in.
5.  Observe that the guest cart items are merged into your authenticated user cart.
6.  Manage items in the cart view.

## URL ENDPOINTS
-------------
- `/`: Homepage with all products.
- `/cart`: View the shopping cart.
- `/login`: User login page.
- `/register`: New user registration page.
- `/profile`: User profile page.

**API Endpoints (used by forms):**
- `POST /cart/items`: Add an item to the cart.
- `PATCH /cart/items/{productId}`: Update an item's quantity in the cart.
- `DELETE /cart/items/{productId}`: Remove an item from the cart.

## NOTES
-----
- The application is seeded with sample product data.
- The guest cart is stored in a cookie named `guest_cart_v1`.
- All core cart logic is extensively tested in `tests/Feature/Cart/CartFlowTest.php`.
