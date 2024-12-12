
<a name="readme-top"></a>

<!-- PROJECT LOGO -->
<br />
<div align="center">
  <a href="https://github.com/sledgehamm3r/sledgePOS">
    <img src="logo.png" alt="Logo" width="80" height="80">
  </a>

  <h3 align="center">sledgePOS</h3>

  <p align="center">
    A modern and simple Point of Sale (POS) system designed for ease of use in small businesses. Build with PHP, CSS, HTML and MySQL
    <br />
    <a href="https://github.com/sledgehamm3r/sledgePOS"><strong>Documentation »</strong></a>
    <br />
    <br />
    <a href="https://github.com/sledgehamm3r/sledgePOS/issues">Report Bug</a>
    ·
    <a href="https://github.com/sledgehamm3r/sledgePOS/issues">Request Feature</a>
  </p>
</div>

<!-- TABLE OF CONTENTS -->
<details>
  <summary>Table of Contents</summary>
  <ol>
    <li>
      <a href="#about-the-project">About the Project</a>
      <ul>
        <li><a href="#built-with">Built With</a></li>
      </ul>
    </li>
    <li><a href="#getting-started">Getting Started</a></li>
    <li><a href="#usage">Usage</a></li>
    <li><a href="#roadmap">Roadmap</a></li>
    <li><a href="#contributing">Contributing</a></li>
    <li><a href="#license">License</a></li>
    <li><a href="#contact">Contact</a></li>
    <li><a href="#acknowledgments">Acknowledgments</a></li>
  </ol>
</details>

<!-- ABOUT THE PROJECT -->
## About the Project

🛠️ **sledgePOS** 🛠️

sledgePOS is a simple, modern, and lightweight Point of Sale (POS) system designed for small businesses to handle inventory management, sales, and customer interactions. It provides features like product search, barcode scanning, and order management.

### Features:
- **Product Management**: Add, update, and delete products with associated prices, categories, and tax rates.
- **Barcode Scanning**: Quickly add products to the cart by scanning barcodes.
- **Order Management**: Keep track of sales and manage payments through cash or card options.
- **Custom Receipt Printing**: Generate and print receipts for customers after payment.
- **Multilingual Support**: Supports multiple languages (currently German and English).

🚀 **How to Use:**
1. **Login**: Use the login page to authenticate yourself as a user.
2. **Select Categories**: Browse or search for products based on categories.
3. **Add Products**: Scan barcodes or search for products to add them to the cart.
4. **Checkout**: Complete the transaction by choosing cash or card payment options.
5. **Print Receipt**: After completing the sale, print a receipt for the customer.

<p align="right">(<a href="#readme-top">Back to Top</a>)</p>

<!-- GETTING STARTED -->
## Getting Started

### Prerequisites:
- PHP 7.4 or later
- MySQL Database
- A web server like Apache or Nginx

### Installation:

1. Download the last release

2. Set up the database:
   - Import the provided `sledgepos.sql` file into your MySQL database to create the necessary tables.
   
3. Update the `config.php` file with your database credentials.

4. Upload the files to your web server and navigate to `index.php` in your browser.

<p align="right">(<a href="#readme-top">Back to Top</a>)</p>

<!-- USAGE -->
## Usage

After installation, you can use sledgePOS to manage products and sales:

- **Login**: Go to the `login.php` page to sign in using your credentials.
- **Browse Products**: On the `index.php` page, view products by category and add them to your cart.
- **Make Payments**: During checkout, select either cash or card as your payment method.
- **Print Receipts**: Generate and print receipts using the `print_receipt.php` page after successful transactions.

<p align="right">(<a href="#readme-top">Back to Top</a>)</p>


## Demo

Adminlogin: 
Username: admin
Password: hallo123

Cashierlogin:
Username: cashier
Password: hallo123

<p align="right">(<a href="#readme-top">Back to Top</a>)</p>

<!-- ROADMAP -->
## Roadmap

- [ ] User Role Management (Admin and Cashier roles)
- [ ] Expanded payment options (e.g., online payments)
- [ ] Integration with external accounting tools
- [ ] Advanced reporting and analytics

<p align="right">(<a href="#readme-top">Back to Top</a>)</p>

<!-- CONTRIBUTING -->
## Contributing

Contributions are welcome! Feel free to fork the repository and create a pull request with your improvements.

<p align="right">(<a href="#readme-top">Back to Top</a>)</p>

<!-- LICENSE -->
## License
Released under the MIT License. For more information, please refer to the LICENSE file.

<p align="right">(<a href="#readme-top">Back to Top</a>)</p>
