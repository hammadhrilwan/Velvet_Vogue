# Velvet Vogue - E-commerce Website

A modern, responsive e-commerce website for Velvet Vogue, a trendy clothing store specializing in casual and formal wear for young adults.

## Features

### 🛍️ Customer Features
- **Product Catalog**: Browse extensive collection of clothing items
- **Advanced Filtering**: Filter by category, gender, size, color, and price
- **Search Functionality**: Smart search with auto-suggestions
- **Shopping Cart**: Session-based cart with quantity management
- **User Accounts**: Registration, login, and profile management
- **Order History**: Track past orders and status
- **Responsive Design**: Optimized for all devices
- **Contact System**: Contact form with FAQ section

### 👩‍💼 Admin Features
- **Admin Dashboard**: Comprehensive management interface
- **Product Management**: Add, edit, and remove products
- **User Management**: View and manage customer accounts
- **Order Management**: Track and update order statuses
- **Statistics**: Sales and inventory insights
- **Secure Access**: Separate admin authentication system

## Technology Stack

- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Backend**: PHP 8+ with PDO
- **Database**: MySQL
- **Architecture**: MVC-inspired separation of concerns
- **Styling**: Custom CSS framework with CSS variables
- **Security**: Password hashing, prepared statements, session management

## Project Structure

```
velvet-vogue/
├── index.html              # Homepage
├── pages/
│   ├── products.html       # Product catalog
│   ├── categories.html     # Category browsing
│   ├── cart.html          # Shopping cart
│   ├── account.html       # Customer accounts
│   └── contact.html       # Contact page
├── admin/
│   ├── login.html         # Admin login
│   ├── register.html      # Admin registration
│   └── dashboard.html     # Admin dashboard
├── css/
│   └── style.css          # Main stylesheet
├── js/
│   └── main.js           # JavaScript functionality
├── php/
│   ├── config.php        # Database configuration
│   ├── auth.php          # Authentication handler
│   ├── products.php      # Product operations
│   ├── cart.php          # Cart management
│   ├── contact.php       # Contact form handler
│   ├── filter_products.php # Product filtering
│   └── search.php        # Search functionality
├── images/               # Product and site images
├── database_setup.sql    # Database schema
├── sitemap.xml          # SEO sitemap
├── robots.txt           # Search engine instructions
└── README.md            # Project documentation
```

## Installation

### Prerequisites
- XAMPP (or similar LAMP/WAMP stack)
- PHP 8.0 or higher
- MySQL 5.7 or higher
- Web browser

### Setup Instructions

1. **Clone/Download the project**
   ```bash
   # Place the project in your web server directory
   # For XAMPP: C:\xampp\htdocs\velvet-vogue\
   ```

2. **Database Setup**
   - Start Apache and MySQL in XAMPP Control Panel
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create a new database named `velvet_vogue`
   - Import the `database_setup.sql` file

3. **Configuration**
   - Open `php/config.php`
   - Update database connection settings if needed:
   ```php
   $host = 'localhost';
   $dbname = 'velvet_vogue';
   $username = 'root';
   $password = '';
   ```

4. **Launch the Website**
   - Open your web browser
   - Navigate to `http://localhost/velvet-vogue/`

## Database Schema

### Main Tables
- **users**: Customer accounts and profiles
- **admin**: Administrative users
- **products**: Product catalog with details
- **categories**: Product categories
- **orders**: Customer orders and status
- **cart**: Shopping cart items
- **contact_messages**: Contact form submissions

## Security Features

- **Password Hashing**: Secure password storage using PHP's password_hash()
- **Prepared Statements**: SQL injection protection
- **Session Management**: Secure user sessions
- **Input Validation**: Client and server-side validation
- **CSRF Protection**: Form token validation
- **Admin Separation**: Separate admin authentication system

## Browser Compatibility

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers

## Development

### CSS Variables
The project uses CSS custom properties for consistent theming:
```css
:root {
    --primary-color: #c9a96e;
    --secondary-color: #2c3e50;
    --accent-color: #e74c3c;
    /* ... more variables */
}
```

### JavaScript Modules
- Cart management
- Form validation
- AJAX operations
- Mobile menu
- Image lazy loading
- FAQ interactions

### PHP Structure
- `config.php`: Database connection and session handling
- `auth.php`: User authentication and registration
- `products.php`: Product CRUD operations
- `cart.php`: Shopping cart functionality
- `contact.php`: Contact form processing

## API Endpoints

### Product Operations
- `GET php/products.php?action=get_all` - Get all products
- `GET php/products.php?action=get_by_category&category=id` - Get products by category
- `POST php/products.php` - Add/update product (admin)

### Cart Operations
- `POST php/cart.php?action=add` - Add item to cart
- `POST php/cart.php?action=remove` - Remove item from cart
- `GET php/cart.php?action=get` - Get cart contents

### Authentication
- `POST php/auth.php?action=login` - User login
- `POST php/auth.php?action=register` - User registration
- `POST php/auth.php?action=logout` - User logout

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## Testing

### Manual Testing Checklist
- [ ] Homepage loads correctly
- [ ] Product catalog with filtering
- [ ] Add to cart functionality
- [ ] User registration/login
- [ ] Checkout process
- [ ] Admin panel access
- [ ] Contact form submission
- [ ] Responsive design on mobile
- [ ] Cross-browser compatibility

### Database Testing
- [ ] All tables created correctly
- [ ] Sample data inserted
- [ ] Foreign key relationships
- [ ] User authentication
- [ ] Product CRUD operations

## Performance Optimization

- Image lazy loading
- CSS minification ready
- JavaScript optimization
- Database query optimization
- Session management

## Future Enhancements

- Payment gateway integration
- Email notifications
- Product reviews and ratings
- Wishlist functionality
- Inventory management
- Multi-language support
- Social media integration
- Advanced analytics

## Support

For support or questions:
- Email: info@velvetvogue.com
- Phone: +94 77 867 2554
- Live Chat: Available on website

## License

This project is created for educational purposes. All product images and content are used for demonstration only.

---

**Velvet Vogue** - Where Fashion Meets Elegance