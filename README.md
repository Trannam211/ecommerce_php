## eCommerce-website-in-PHP

A fully functional project based on an Online  Ecommerce Website that uses PHP Language with  MySQL database. Following PHP  MySQL project contains all the essential features which can be in use for the final-year IT students for their college projects. It has a number of features that will allow users to manage products and shop online.

<img width="949" alt="Screenshot 2024-07-09 130858" src="https://github.com/user-attachments/assets/7dd4bf52-7c73-44f1-83cb-58e828bc8142">

## Technologies:

HTML: Page layout and design

CSS: Design

JS: Fontend

PHP: Backend

Bootstrap

Java Script

## Supported Operating Systems:
You can run this project in the following operating systems:

Windows

MAC

Linux

## Features:

1-Client-Side Interaction.

2-Admin Panel.

3-Customer Registration.

4-Add to Cart System.

5-Checkout System.

6-Update Billing Address.

7-Search, Filter Products.

8-Product Categories.

9-Featured Products.

10-Latest Products.

11-Popular Products.

12-View Order History.

13-Update Customer Profile.

14-Manage Website Settings.

15-Manage Shop Settings.

16-Product Management.

17-Order Management.

18-Pending Orders.

19-Shippings.

20-Set up Image Sliders.

21-Set Shipping Charges.

22-Page Settings.

23-Set Social Media Links.

24-Subscribers.

25-View Registered Customers.

## Featured, Latest and Popular Products:.

When it comes to projects such as  eCommerce, it is mandatory thing that a website should include these sections for user satisfaction. Not just satisfaction, it also helps to develop a two-way relationship between the end-users and the website.

<img width="939" alt="Screenshot 2024-07-18 155503" src="https://github.com/user-attachments/assets/5f725c04-157f-4ed9-aa73-13b1f9c35d4c">

Speaking of it, the featured section helps the end-users to interact with their products, either way, to boost it up as well. This particular section tends to attract users to their featured products. Likewise, as the name itself suggest the latest products, it displays a certain number of recently added products on the client-side.

<img width="951" alt="Screenshot 2024-07-18 155402" src="https://github.com/user-attachments/assets/22c78a1a-bd62-4473-b043-afbbe1986c51">

Similarly, this  eCommerce website project counts total views on a certain product in order to display under popular category. Meaning, all the most-viewed products are automatically listed under it. In fact, these sections are totally controllable by the system administrator including their number of display products.

<img width="944" alt="Screenshot 2024-07-18 155612" src="https://github.com/user-attachments/assets/3f459e5b-adf1-4a67-9b1d-ec3586d83dba">

## Cart, Product Category, View Customer’s Order and More:

Talking about the product category, the customers can simply switch product sections with a simple click. With it, the website displays all the available products under the requested category. For instance, clicking on the men’s section displays products related to men and so on.

This whole website contains a system to top and end level navigation for easy user interaction within the website. Also, a cart system is available for the users in order to add products to it for the checkout procedure. Also, this particular section contains all the necessary features such as updating the cart, removing items, and more.

<img width="946" alt="Screenshot 2024-07-18 155825" src="https://github.com/user-attachments/assets/e7023ca6-21a6-46de-9e99-433061b0fcc0">

## Admin Panel:

Now moving towards the admin panel, an administrator has full control over the system. An admin has the right to manage the proper flow of the system. He/she can manage website settings, image sliders, products, orders, customers, and more. Speaking of website settings, it means an admin can update header-footer settings, website brandings, and other website details.

Also, the admin has to manage shop settings such as color, size, shipping cost, categories. In-depth, the category refers to top, mid, and end level categories for displaying products. Each product needs to have a top, mid, and end level category just like a host for management of it. In fact, these are the exact point which displays under the website’s navigation bar. Besides, an administrator can manage image sliders, page settings, services sections for the client-side of things.

<img width="959" alt="Screenshot 2024-07-18 154915" src="https://github.com/user-attachments/assets/270be569-55dd-4be5-a9a4-9694984d9441">

## Installation:

After Starting Apache and MySQL in XAMPP, follow the following steps:

1st Step: Extract file

2nd Step: Copy the main project folder

3rd Step: Paste in xampp/htdocs/

Now Connecting Database 4th Step: Open a browser and go to URL “http://localhost/phpmyadmin/”

5th Step: Click on the databases tab

6th Step: Create a database named “ecommerceweb” and then click on the import tab

7th Step: Click on browse file and select “ecommerceweb.sql” file inside “database/ecommerceweb.sql”

8th Step: Click on go.

After Creating Database,

9th Step: Open a browser and go to URL “http://localhost/eCommerce-website-in-PHP”

## Project Structure (Updated)

The project has been reorganized to separate shared frontend includes, database assets, docs, and tests.

```
eCommerce-website-in-PHP-main/
|-- admin/                     # Admin panel pages and admin assets
|-- app/
|   `-- frontend/
|       `-- shared/            # Shared frontend partials (header/footer/sidebars)
|-- assets/                    # Public static assets for frontend
|-- database/
|   `-- ecommerceweb.sql       # SQL dump
|-- docs/
|   |-- project-info/          # Login details and setup-related notes
|   `-- screenshots/           # Project screenshots
|-- payment/                   # Bank/PayPal payment integrations
|-- scripts/                   # Utility scripts
|-- tests/
|   `-- manual/                # Manual test pages/files
`-- *.php                      # Public entry pages
```

Compatibility note:
Root files `header.php`, `footer.php`, `customer-sidebar.php`, and `sidebar-category.php` are now lightweight bridge files that load implementations from `app/frontend/shared/` to keep existing page includes working.

Need Help or Customization?

If you need assistance with setup, customization, or any other support, feel free to contact me. I’m here to help and provide the best solutions for your needs.
