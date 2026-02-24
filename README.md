# Inventory Management System with Accounting Integration

## Overview
A comprehensive inventory management system built with Laravel 11+ that implements proper double-entry bookkeeping for all transactions. The system automatically generates journal entries for stock movements and sales, ensuring accurate financial reporting.

## Features

### 1. Product Management
- Add, edit, and delete products
- Track SKU, name, category, purchase price, sell price
- Set opening stock with automatic journal entry
- Real-time stock quantity tracking

### 2. Sales Management
- Create sales with multiple products
- Apply discounts and VAT automatically
- Support partial payments (track due amounts)
- Automatic stock updates and COGS calculation
- Generate journal entries for each sale

### 3. Double-Entry Accounting
- Complete chart of accounts (Assets, Liabilities, Equity, Revenue, Expense)
- Automatic journal entry creation for:
  - Opening stock (DR Inventory, CR Retained Earnings)
  - Sales transactions (DR Cash/AR, CR Sales Revenue, DR COGS, CR Inventory)
  - VAT collection (CR VAT Payable)
- Balance sheet and P&L reporting

### 4. Financial Reports
- **Financial Report**: Total sales, expenses, COGS, gross profit with date filters
- **Journal Entries**: View all accounting transactions with debit/credit details
- **Inventory Report**: Stock levels, values, and potential revenue
- **Chart of Accounts**: All accounts with current balances and accounting equation verification

### 5. Dashboard
- Overview statistics (products, stock value, sales)
- Recent sales transactions
- Low stock alerts
- Quick action buttons

## Technical Specifications

### Requirements
- PHP 8.1 or higher
- Laravel 11.x
- MySQL 5.7+ or MariaDB 10.3+
- Composer
- XAMPP (for local development on Windows)

### Database Schema
```
accounts (16 predefined accounts)
├── id, code, name, type, normal_balance, description

products
├── id, sku, name, category, purchase_price, sell_price
├── stock_quantity, description

journal_entries
├── id, entry_date, type, description

journal_lines
├── id, journal_entry_id, account_id, type, amount

sales
├── id, invoice_number, customer_name, sale_date
├── subtotal, discount_amount, vat_percentage, vat_amount
├── total_amount, paid_amount, due_amount, journal_entry_id

sale_items
├── id, sale_id, product_id, quantity, unit_price, total_price

stock_movements
├── id, product_id, type, quantity, reference_type, reference_id, notes
```

## Installation Instructions

### Step 1: Extract and Setup
1. Extract the project to `C:\xampp\htdocs\ZaviSoft\inventory-management`
2. Navigate to the project directory

### Step 2: Configure Environment
1. Ensure `.env` file exists with these settings:
```env
APP_NAME="Inventory Management System"
APP_ENV=local
APP_KEY=base64:YourGeneratedKeyHere
APP_DEBUG=true
APP_URL=http://localhost:8001

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=inventory_management
DB_USERNAME=root
DB_PASSWORD=
```

### Step 3: Install Dependencies
```bash
cd c:\xampp\htdocs\ZaviSoft\inventory-management
composer install
composer dump-autoload
```

### Step 4: Setup Database
1. Start XAMPP Apache and MySQL services
2. Import the database:
```bash
C:\xampp\mysql\bin\mysql.exe -u root < database_setup.sql
```

Or use phpMyAdmin:
- Open http://localhost/phpmyadmin
- Create database "inventory_management"
- Import `database_setup.sql`

### Step 5: Start Laravel Server
```bash
php artisan serve --port=8001
```

### Step 6: Access the Application
Open your browser and navigate to:
```
http://127.0.0.1:8001
```

## Business Scenario Example

### Sample Product
- **Name**: Sample Product
- **SKU**: PRD001
- **Purchase Price**: ৳100.00
- **Sell Price**: ৳200.00
- **Opening Stock**: 50 units

**Opening Stock Journal Entry:**
- DR Inventory: ৳5,000.00
- CR Retained Earnings: ৳5,000.00

### Sample Sale
**Sale Details:**
- Product: Sample Product (10 units @ ৳200.00)
- Subtotal: ৳2,000.00
- Discount: ৳50.00
- VAT (5%): ৳97.50
- **Total**: ৳2,047.50
- Paid: ৳1,000.00
- Due: ৳1,047.50

**Automatic Journal Entry:**
1. DR Cash: ৳1,000.00
2. DR Accounts Receivable: ৳1,047.50
3. CR Sales Revenue: ৳2,047.50
4. CR VAT Payable: ৳97.50
5. DR Cost of Goods Sold: ৳1,000.00 (10 × ৳100)
6. CR Inventory: ৳1,000.00

**Stock Update:**
- Before: 50 units
- After: 40 units

## Accounting Principles

### Double-Entry Bookkeeping
Every transaction affects at least two accounts. Total debits must equal total credits.

### Account Types and Normal Balances
- **Assets** (Debit): Cash, Accounts Receivable, Inventory
- **Liabilities** (Credit): Accounts Payable, VAT Payable
- **Equity** (Credit): Retained Earnings
- **Revenue** (Credit): Sales Revenue
- **Expense** (Debit): Cost of Goods Sold, Operating Expenses

### Accounting Equation
```
Assets = Liabilities + Equity + (Revenue - Expenses)
```

## Documentation

- **README.md** (this file) - Complete system overview and installation  
- **BUSINESS_SCENARIO.md** - Detailed walkthrough of business scenario
- **ACCOUNTING_GUIDE.md** - Accounting concepts and journal entry explanations
- **DEPLOYMENT.md** - Production deployment checklist

## Quick Start

1. Start XAMPP (Apache + MySQL)
2. Import database: `mysql -u root < database_setup.sql`
3. Navigate to project: `cd C:\xampp\htdocs\ZaviSoft\inventory-management`
4. Start server: `php artisan serve --port=8001`
5. Open browser: http://127.0.0.1:8001
6. Add a product with opening stock
7. Create a sale
8. View reports

## Support

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
"# inventory-management" 
