<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laundry Systems | Laundry System</title>
    <link rel="stylesheet" href="../system/css/home.css">
    <style>
        /* Slideshow container should be centered and have a max width */
.slideshow-container {
    width: 550px;             /* Full width of the page */
    max-width: 800px;        /* You can adjust this to your desired max width */
    margin: 0 auto;          /* Centers the container */
    overflow: hidden;        /* Prevents images from overflowing */
    padding-top: 50px;
    padding-left: 50px;
}

/* Slideshow images will take 100% width of the container */
.slideshow-container img {
    width: 100%;             /* Ensures images fill the width of the container */
    height: 300px;           /* Set the fixed height to be a bit smaller */
    object-fit: cover;       /* Ensure the image fills the area without distortion */
    border: 5px solid #ddd;  /* Optional: Adds a border */
    border-radius: 8px;      /* Optional: Rounds the corners */
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2); /* Optional: Adds a shadow */
}

    </style>
</head>
<body>

    <!-- Header Section -->
    <header>
    <h1>Laundry Shop</h1>
    <nav>
        <ul>
            <li class="dropdown">
                <a href="#">Who We Serve <span class="dropdown-arrow">▾</span></a>
                <div class="dropdown-content">
                    <a href="#">Students</a>
                    <a href="#">Workers</a>
                    <a href="#">Senior Citizen / PWD's</a>
                </div>
            </li>
            <li class="dropdown">
                <a href="#">Laundry Equipment <span class="dropdown-arrow">▾</span></a>
                <div class="dropdown-content">
                    <a href="#">Washing Machines</a>
                    <a href="#">Dryers</a>
                    <a href="#">Folding Machines</a>
                    <a href="#">Ironing and Finishing Equipment</a>
                    <a href="#">Stain Removal and Spotting Stations</a>
                </div>
            </li>
            <li class="dropdown">
                <a href="#">Services <span class="dropdown-arrow">▾</span></a>
                <div class="dropdown-content">
                    <a href="#">Pick up & Delivery</a>
                    <a href="#">Wash & fold</a>
                    <a href="#">Dry Cleaning</a>
                </div>
            </li>
            <li class="dropdown">
                <!-- Changed 'booking.php' to '#' or a dedicated 'about-us.php' if it exists -->
                <a href="#">About Us <span class="dropdown-arrow">▾</span></a>
                <div class="dropdown-content">
                    <a href="#">Company Overview</a>
                    <a href="#">Our Mission & Values</a>
                    <a href="#">Quality & Care:</a>
                    <a href="#">Location</a>
                </div>
            </li>
        </ul>
    </nav>
    <div class="btn-user">
        <a href="login.php">User</a>
        <a href="../system/admin/admin.php">Admin</a>
    </div>
</header>


    <!-- Information Section with Form -->
    <section class="info-section">
        <div class="info-content">
            <div class="heading-container">
                <h2 class="first">Track, Wash, Relax</h2>
                <h2 class="second">Track, Wash, Relax</h2>
            </div>
            <p class="main-text">Laundry Made Simple</p>
            <p>Handles your garments with the utmost care and attention, ensuring thorough cleaning, ironing, and folding.</p>

            <div class="button-group">
                <button onclick="scrollToForm()" class="book-now-btn">Book Now</button>
                <button class="schedule-btn"><a href="../system/contact.php">Contact Us</a></button> <!-- Corrected link -->
            </div>
        </div>

        <div class="slideshow-container">

<div class="mySlides fade">
    <img src="../system/img/slideshow1.jpg" style="width:100%; max-height: 500px; border: 5px solid #ddd; border-radius: 8px; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);">
</div>

<div class="mySlides fade">
    <img src="../system/img/slideshow2.jpg" style="width:100%; max-width: 100%; overflow:hidden; border: 5px solid #ddd; border-radius: 8px; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);">
</div>

<div class="mySlides fade">
    <img src="../system/img/slideshow3.jpg" style="width:100%; max-height: 500px; border: 5px solid #ddd; border-radius: 8px; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);">
</div>

</div>
<br>

<div style="text-align:center">
  <span class="dot"></span>
  <span class="dot"></span>
  <span class="dot"></span>
</div>

<script>
let slideIndex = 0;
showSlides();

function showSlides() {
  let i;
  let slides = document.getElementsByClassName("mySlides");
  let dots = document.getElementsByClassName("dot");
  for (i = 0; i < slides.length; i++) {
    slides[i].style.display = "none";
  }
  slideIndex++;
  if (slideIndex > slides.length) {slideIndex = 1}
  for (i = 0; i < dots.length; i++) {
    dots[i].className = dots[i].className.replace(" active", "");
  }
  slides[slideIndex-1].style.display = "block";
  dots[slideIndex-1].className += " active";
  setTimeout(showSlides, 2000); // Change image every 2 seconds
}

// Dummy function - implement actual scroll to form if a form is added
function scrollToForm() {
    console.log("Scroll to form functionality needs to be implemented.");
    // Example: window.location.href = '#your-form-id';
}
</script>

    </section>


    <!-- Services Section -->
   <section class="services-section">
    <h2>Services We Offer</h2>
    <div class="services-container">
        <ul class="services-list">
            <li>
                <div class="service-item">
                    <img src="../system/img/fold.jpg" alt="Wash and Fold Services" class="service-image">
                    <div class="service-description">
                        <h3>Wash and Fold Services</h3>
                        <p>We provide convenient wash and fold services, ensuring your clothes are thoroughly cleaned, neatly folded, and ready for you to pick up or have delivered.</p>

                </div>
            </li>
            <li>
                <div class="service-item">

                    <div class="service-description">
                        <h3 style="margin-left: 140px; width: fit-content;">Dry Cleaning</h3>
                        <p style="margin-left: 140px; width: fit-content;">Our professional dry cleaning service uses advanced techniques to clean delicate fabrics, ensuring they look their best without damage.</p>
                    </div>
                    <img src="../system/img/dry-cleaning.jpg" alt="Dry Cleaning" class="service-image">
                </div>
            </li>
            <li>
                <div class="service-item">
                    <img src="../system/img/towel.jpg" alt="Linen and Towel Service" class="service-image">
                    <div class="service-description">
                        <h3>Linen and Towel Service</h3>
                        <p>We offer specialized cleaning for linens and towels, perfect for hotels, restaurants, and businesses looking to maintain high standards of hygiene and appearance.</p>
                </div>
            </li>
            <li>
                <div class="service-item">

                    <div class="service-description">
                        <h3 style="margin-left: 140px; width: fit-content;">Specialty Item Cleaning</h3>
                        <p style="margin-left: 140px; width: fit-content;">We handle specialty items such as wedding dresses, rugs, and upholstery with utmost care, utilizing appropriate techniques for each type of fabric.</p>
                    </div>
                    <img src="../system/img/clean.jpeg" alt="Specialty Item Cleaning" class="service-image">
                </div>
            </li>
            <li>
                <div class="service-item">
                    <img src="../system/img/stain-removal.jpg" alt="Stain Removal" class="service-image">
                    <div class="service-description">
                        <h3>Stain Removal</h3>
                        <p>Our stain removal service effectively treats tough stains, ensuring your garments return to you spotless and fresh.</p>
                    </div>

            </li>
            <li>
                <div class="service-item">

                    <div class="service-description">
                        <h3 style="margin-left: 140px; width: fit-content;">Pickup and Delivery Services</h3>
                        <p style="margin-left: 140px; width: fit-content;">Enjoy the convenience of our pickup and delivery services, making laundry hassle-free by bringing your laundry directly to your doorstep.</p>
                    </div>
                    <img src="../system/img/pick-up.jpeg" alt="Pickup and Delivery Services" class="service-image">
                </div>
            </li>
            <li>
                <div class="service-item">
                    <img src="../system/img/self.jpg" alt="Self-Service Laundry" class="service-image">
                    <div class="service-description">
                        <h3>Self-Service Laundry</h3>
                        <p>For those who prefer to do it themselves, our self-service laundry facilities are equipped with modern machines for your convenience.</p>
                    </div>

            </li>
            <li>
                <div class="service-item">

                    <div class="service-description">
                        <h3 style="margin-left: 140px; width: fit-content;">Eco-Friendly Cleaning Options</h3>
                        <p style="margin-left: 140px; width: fit-content;">We offer eco-friendly cleaning solutions that are gentle on the environment while providing excellent results for your garments.</p>
                    </div>
                    <img src="../system/img/eco.jpg" alt="Eco-Friendly Cleaning Options" class="service-image">
                </div>
            </li>
        </ul>
    </div>
</section>

    <footer>
	<ul class="social-icon">
      <li class="social-icon__item"><a class="social-icon__link" href="#">
          <ion-icon name="logo-facebook"></ion-icon>
        </a></li>
      <li class="social-icon__item"><a class="social-icon__link" href="#">
          <ion-icon name="logo-twitter"></ion-icon>
        </a></li>
      <li class="social-icon__item"><a class="social-icon__link" href="#">
          <ion-icon name="logo-linkedin"></ion-icon>
        </a></li>
      <li class="social-icon__item"><a class="social-icon__link" href="#">
          <ion-icon name="logo-instagram"></ion-icon>
        </a></li>
    </ul>
        <p style=" color: black;">© 2024 Laundry Shop. All rights reserved.</p>
        <p style=" color: black;">Follow us on
            <a href="#" style="color: #3498db;">Facebook</a>,
            <a href="#" style="color: #3498db;">Twitter</a>,
            <a href="#" style="color: #3498db;">Instagram</a>
        </p>
    </footer>

	 <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
  <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>


</body>
</html>