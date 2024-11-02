# myNITM Essential

myNITM Essential is a Laravel-based web application designed to streamline campus services and enhance convenience for students and staff. Tailored for the National Institute of Technology Meghalaya (NITM), this application provides an integrated solution to manage lost and found items, secure payments, and fee receipt management within the institute.

## Key Features

### Lost and Found Tracking
- Enables students and staff to report lost or found items within the campus.
- Users can search for specific lost items, view detailed descriptions, and connect with the item's finder.
- Reduces the hassle of physical lost and found boards, providing a systematic way to manage lost items.

### Secure Payment System
- Facilitates secure, streamlined payments for various on-campus fees and services.
- Supports online transactions, with multiple payment options, ensuring convenience and security for users.
- Integrates payment records with user profiles for easy reference and tracking.

### Fee Receipt Management
- Allows students to access, download, and manage fee receipts and payment confirmations.
- Offers a digital record of all transactions, making it easier for students to keep track of their financial obligations and payment history.
- Simplifies administrative tasks by reducing paper-based receipts, improving efficiency, and enhancing accessibility.

## Tech Stack
- **Backend:** PHP (Laravel)
- **Frontend:** JavaScript, HTML, CSS, SCSS

## Installation

To set up the project locally, follow these steps:

1. **Clone the repository:**
   ```bash
   git clone https://github.com/sadreal/myNITMessentials.git
   cd myNITMessentials
   ```

2. **Install dependencies:**
   Ensure you have [Composer](https://getcomposer.org/) installed, then run:
   ```bash
   composer install
   ```

3. **Set up your environment file:**
   Copy the `.env.example` file to `.env`:
   ```bash
   cp .env.example .env
   ```

4. **Generate the application key:**
   ```bash
   php artisan key:generate
   ```

5. **Set up the database:**
   Update the `.env` file with your database configuration:
   ```plaintext
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=your_database_name
   DB_USERNAME=your_database_username
   DB_PASSWORD=your_database_password
   ```

6. **Run migrations:**
   ```bash
   php artisan migrate
   ```

7. **Start the local server:**
   ```bash
   php artisan serve
   ```

   You can access the application at `http://localhost:8000`.

## Usage

Once the application is up and running, you can:
- Report lost or found items.
- Make secure payments for on-campus services.
- Manage your fee receipts and payment history.

## Contributing

Contributions are welcome! Please feel free to submit a pull request or raise an issue for any enhancements or bugs.


## Contact

For any inquiries or feedback, please contact:
- Author(s):
- GitHub: [sadreal](https://github.com/sadreal) , [koyelkalita](https://github.com/koyelkalita), [shefukri](https://github.com/shefukri)
