<?php
$page_title = "About Us - Serendib Pathways";
include 'includes/header.php';
?>


<!-- Swiper CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
<style>
  /* Make Swiper pagination bullets white */
  .swiper-pagination-bullet {
    background: #fff !important;
    opacity: 0.8;
  }
  .swiper-pagination-bullet-active {
    background: #fff !important;
    opacity: 1;
  }
  
  /* Hero section styles */
  .hero-image {
    position: absolute;
    top: 0;
    width: 300px;
    height: 100%;
    object-fit: cover;
    opacity: 0.3;
  }
  
  .hero-image-left {
    left: 0;
  }
  
  .hero-image-right {
    right: 0;
  }
  
  /* Trapezoid styles */
  .trapezoid-left {
    background: white;
    clip-path: polygon(0 0, 100% 0, 85% 100%, 0 100%);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    width: 60%;
  }
  
  .trapezoid-right {
    background: white;
    clip-path: polygon(15% 0, 100% 0, 100% 100%, 0% 100%);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    width: 60%;
    margin-left: auto;
  }
  
  .trapezoid-content {
    padding: 2rem;
  }

  .team-portrait {
    position: relative;
    height: 19rem;
    overflow: hidden;
    background: linear-gradient(145deg, #0d4c3c, #c9a74d);
  }

  .team-portrait img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center 28%;
    transition: transform .7s cubic-bezier(.2,.8,.2,1);
  }

  .team-portrait::after {
    content: '';
    position: absolute;
    inset: auto 0 0;
    height: 38%;
    background: linear-gradient(transparent, rgba(5,45,35,.5));
    pointer-events: none;
  }

  .team-card {
    transition: transform .35s ease, box-shadow .35s ease;
  }

  .team-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 24px 55px rgba(16, 55, 43, .16);
  }

  .team-card:hover .team-portrait img {
    transform: scale(1.045);
  }
  
  @media (max-width: 768px) {
    .hero-image {
      display: none;
    }
    
    .trapezoid-left,
    .trapezoid-right {
      margin: 0 0 2rem 0;
      clip-path: none;
      border-radius: 0.5rem;
      width: 100%;
    }
  }
</style>


<main>
   <!-- Hero Section: Full Background Image Version -->
<section class="relative w-full h-[335px] md:h-[260px] bg-gradient-to-r from-green-600 to-blue-600 flex items-center justify-center text-white overflow-hidden">
    <!-- Full background image using absolute positioning and object-cover -->
    <img src="assets/about-6.jpg" alt="Serendib Pathways"
         class="absolute inset-0 w-full h-full object-cover object-center opacity-60 pointer-events-none select-none" style="z-index: 1;">


    <!-- Overlay content -->
    <div class="relative z-10 w-full text-center">
        <h1 class="text-5xl font-bold mb-6 drop-shadow-lg">About Serendib Pathways</h1>
        <p class="text-xl max-w-3xl mx-auto drop-shadow">
            Passionate about sustainable tourism and preserving Sri Lanka's natural heritage for future generations.
        </p>
    </div>


    <!-- Optional: additional overlay if needed for better text contrast -->
    <div class="absolute inset-0 bg-gradient-to-b from-green-700/50 to-blue-800/40 z-5"></div>
</section>



    <!-- About Content -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div>
                    <h2 class="text-4xl font-bold text-gray-800 mb-6">Our Story</h2>
                    <p class="text-gray-600 mb-4">Founded in 2015, Serendib Pathways was born from a deep love for Sri Lanka's incredible biodiversity and rich cultural heritage. We recognized the need for responsible tourism that benefits both visitors and local communities while protecting our precious environment.</p>
                    <p class="text-gray-600 mb-4">Our team consists of passionate locals who have spent years exploring every corner of this beautiful island. We believe that tourism should be a force for good, creating positive impacts on local economies while preserving the natural wonders that make Sri Lanka so special.</p>
                    <p class="text-gray-600">Today, we're proud to be one of Sri Lanka's leading eco-tourism operators, having welcomed thousands of travelers from around the world to experience the authentic beauty of Ceylon.</p>
                </div>


                <!-- Swiper Carousel for Our Story Images -->
                <div class="swiper w-full h-96 rounded-lg overflow-hidden">
                    <div class="swiper-wrapper">
                        <?php
                        for ($i = 1; $i <= 7; $i++) {
                            echo '
                            <div class="swiper-slide">
                                <img src="assets/about-' . $i . '.jpg" alt="Our Story Image ' . $i . '" class="w-full h-96 object-cover" />
                            </div>';
                        }
                        ?>
                    </div>
                    <!-- Swiper Pagination -->
                    <div class="swiper-pagination"></div>
                </div>
            </div>
        </div>
    </section>


    <!-- Mission & Vision with Trapezoids -->
    <section class="py-16 bg-gray-100">
        <div class="w-full">
            <!-- Vision - Full Width Row (Now First) -->
            <div class="trapezoid-right mb-8">
                <div class="trapezoid-content text-right">
                    <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mb-6 ml-auto">
                        <i class="fas fa-eye text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Our Vision</h3>
                    <p class="text-gray-600">To be the leading eco-tourism company in Sri Lanka, setting the standard for responsible travel that preserves our environment and empowers local communities for generations to come.</p>
                </div>
            </div>
            
            <!-- Mission - Full Width Row (Now Second) -->
            <div class="trapezoid-left">
                <div class="trapezoid-content">
                    <div class="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mb-6">
                        <i class="fas fa-bullseye text-green-600 text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Our Mission</h3>
                    <p>
  To provide authentic, sustainable tourism experiences that showcase Sri Lanka's  natural beauty  <br>
 and cultural richness while supporting local communities and conservation efforts.
</p>


</div>
            </div>
        </div>
    </section>


    <!-- Values -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">Our Values</h2>
                <p class="text-xl text-gray-600">The principles that guide everything we do</p>
            </div>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="text-center p-6">
                    <div class="bg-green-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-leaf text-green-600 text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-3">Sustainability</h3>
                    <p class="text-gray-600">Every tour is designed to minimize environmental impact and promote conservation.</p>
                </div>
                <div class="text-center p-6">
                    <div class="bg-blue-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-handshake text-blue-600 text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-3">Community</h3>
                    <p class="text-gray-600">We work closely with local communities to ensure tourism benefits everyone.</p>
                </div>
                <div class="text-center p-6">
                    <div class="bg-yellow-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-heart text-yellow-600 text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-3">Authenticity</h3>
                    <p class="text-gray-600">We showcase the real Sri Lanka, from hidden gems to cultural traditions.</p>
                </div>
            </div>
        </div>
    </section>


    <!-- Team Section -->
    <section class="py-16 bg-gray-100">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">Meet Our Team</h2>
                <p class="text-xl text-gray-600">The passionate people behind your unforgettable experiences</p>
            </div>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="team-card bg-white rounded-lg shadow-lg overflow-hidden text-center">
                    <div class="team-portrait">
                        <img src="assets/images/team/jayanath-kularoosiya.jpg" alt="Jayanath Kulasooriya, Founder and CEO of Serendib Pathways" loading="lazy">
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-semibold mb-2">Jayanath Kulasooriya</h3>
                        <p class="text-green-600 mb-3">Founder & CEO</p>
                        <p class="text-gray-600 text-sm">Founder and strategic leader of Serendib Pathways.</p>
                    </div>
                </div>
                <div class="team-card bg-white rounded-lg shadow-lg overflow-hidden text-center">
                    <div class="h-64 bg-gradient-to-br from-blue-400 to-purple-500"></div>
                    <div class="p-6">
                        <h3 class="text-xl font-semibold mb-2">Sandaru Dinusha</h3>
                        <p class="text-green-600 mb-3">Head of Operations</p>
                        <p class="text-gray-600 text-sm">Leading daily operations and creating seamless journeys across Sri Lanka.</p>
                    </div>
                </div>
                <div class="team-card bg-white rounded-lg shadow-lg overflow-hidden text-center">
                    <div class="team-portrait">
                        <img src="assets/images/team/mahesh-operations-manager.jpg" alt="Mahesh Fernando, Operations Manager at Serendib Pathways" loading="lazy">
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-semibold mb-2">Mahesh Fernando</h3>
                        <p class="text-green-600 mb-3">Operations Manager</p>
                        <p class="text-gray-600 text-sm">Overseeing tour operations and exceptional guest experiences.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>


<!-- Swiper JS -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
    const swiper = new Swiper('.swiper', {
        loop: true,
        autoplay: {
            delay: 3000,
            disableOnInteraction: false,
        },
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        // Navigation removed per request
    });
</script>

<?php include 'includes/footer.php'; ?>
