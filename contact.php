<?php
$page_title = "Contact Us - Serendib Pathways";
require_once 'config/database.php';

$message = '';
$message_type = '';
$requestedExperience = is_string($_GET['experience'] ?? null) ? trim($_GET['experience']) : '';
$initialMessage = $requestedExperience !== '' ? 'I would like to plan ' . mb_substr($requestedExperience, 0, 160) . '. Please share more details.' : '';

if ($_POST) {
    $database = new Database();
    $db = $database->getConnection();
    
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $phone = trim($_POST['phone']);
    $country = trim($_POST['country']);
    $interest = trim($_POST['interest']);
    $travel_date = trim($_POST['travel_date']);
    $group_size = trim($_POST['group_size']);
    $msg = trim($_POST['message']);
    $newsletter = isset($_POST['newsletter']) ? 1 : 0;
    
    $name = $first_name . ' ' . $last_name;
    $subject = $interest ? "Interest in " . $interest : "General Inquiry";
    
    if ($first_name && $last_name && $email && $msg) {
        $query = "INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        
        $full_message = "Name: $name\nEmail: $email\nPhone: $phone\nCountry: $country\nInterest: $interest\nTravel Date: $travel_date\nGroup Size: $group_size\n\nMessage:\n$msg";
        
        if ($stmt->execute([$name, $email, $subject, $full_message])) {
            $message = "Thank you for your message! We'll get back to you soon.";
            $message_type = "success";
        } else {
            $message = "Sorry, there was an error sending your message. Please try again.";
            $message_type = "error";
        }
    } else {
        $message = "Please fill in all required fields.";
        $message_type = "error";
    }
}

include 'includes/header.php';
?>

    <!-- Hero Section -->
<section class="relative bg-cover bg-center bg-no-repeat text-white py-20" style="background-image: url('assets/contact.jpg');">
    <div class="absolute inset-0 bg-gradient-to-r from-green-800 via-green-600 to-blue-600 opacity-20"></div>
    <div class="relative container mx-auto px-4 text-center">
        <h1 class="text-5xl font-bold mb-6">Get In Touch</h1>
        <p class="text-xl max-w-3xl mx-auto">Ready to start your Sri Lankan adventure? We're here to help you plan the perfect eco-friendly journey.</p>
    </div>
</section>


    <!-- Contact Section -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="grid md:grid-cols-2 gap-12">
                <!-- Contact Form -->
                <div>
                    <h2 class="text-3xl font-bold text-gray-800 mb-6">Send Us a Message</h2>
                    
                    <?php if ($message): ?>
                        <div class="mb-6 p-4 rounded-lg <?php echo $message_type == 'success' ? 'bg-green-100 text-green-700 border border-green-300' : 'bg-red-100 text-red-700 border border-red-300'; ?>">
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" class="space-y-6">
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">First Name *</label>
                                <input type="text" id="first_name" name="first_name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent" value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>">
                            </div>
                            <div>
                                <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">Last Name *</label>
                                <input type="text" id="last_name" name="last_name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent" value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>">
                            </div>
                        </div>
                        
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
                            <input type="email" id="email" name="email" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>
                        
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                            <input type="tel" id="phone" name="phone" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                        </div>
                        
                        <div>
                            <label for="country" class="block text-sm font-medium text-gray-700 mb-2">Country</label>
                            <select id="country" name="country" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                <option value="">Select your country</option>
                                <?php
                                $countries = [
                                    "AF" => "Afghanistan",
                                    "AL" => "Albania",
                                    "DZ" => "Algeria",
                                    "AD" => "Andorra",
                                    "AO" => "Angola",
                                    "AG" => "Antigua and Barbuda",
                                    "AR" => "Argentina",
                                    "AM" => "Armenia",
                                    "AU" => "Australia",
                                    "AT" => "Austria",
                                    "AZ" => "Azerbaijan",
                                    "BS" => "Bahamas",
                                    "BH" => "Bahrain",
                                    "BD" => "Bangladesh",
                                    "BB" => "Barbados",
                                    "BY" => "Belarus",
                                    "BE" => "Belgium",
                                    "BZ" => "Belize",
                                    "BJ" => "Benin",
                                    "BT" => "Bhutan",
                                    "BO" => "Bolivia",
                                    "BA" => "Bosnia and Herzegovina",
                                    "BW" => "Botswana",
                                    "BR" => "Brazil",
                                    "BN" => "Brunei",
                                    "BG" => "Bulgaria",
                                    "BF" => "Burkina Faso",
                                    "BI" => "Burundi",
                                    "CV" => "Cabo Verde",
                                    "KH" => "Cambodia",
                                    "CM" => "Cameroon",
                                    "CA" => "Canada",
                                    "CF" => "Central African Republic",
                                    "TD" => "Chad",
                                    "CL" => "Chile",
                                    "CN" => "China",
                                    "CO" => "Colombia",
                                    "KM" => "Comoros",
                                    "CD" => "Congo, Democratic Republic of the",
                                    "CG" => "Congo, Republic of the",
                                    "CR" => "Costa Rica",
                                    "CI" => "Côte d'Ivoire",
                                    "HR" => "Croatia",
                                    "CU" => "Cuba",
                                    "CY" => "Cyprus",
                                    "CZ" => "Czechia",
                                    "DK" => "Denmark",
                                    "DJ" => "Djibouti",
                                    "DM" => "Dominica",
                                    "DO" => "Dominican Republic",
                                    "EC" => "Ecuador",
                                    "EG" => "Egypt",
                                    "SV" => "El Salvador",
                                    "GQ" => "Equatorial Guinea",
                                    "ER" => "Eritrea",
                                    "EE" => "Estonia",
                                    "SZ" => "Eswatini",
                                    "ET" => "Ethiopia",
                                    "FJ" => "Fiji",
                                    "FI" => "Finland",
                                    "FR" => "France",
                                    "GA" => "Gabon",
                                    "GM" => "Gambia",
                                    "GE" => "Georgia",
                                    "DE" => "Germany",
                                    "GH" => "Ghana",
                                    "GR" => "Greece",
                                    "GD" => "Grenada",
                                    "GT" => "Guatemala",
                                    "GN" => "Guinea",
                                    "GW" => "Guinea-Bissau",
                                    "GY" => "Guyana",
                                    "HT" => "Haiti",
                                    "HN" => "Honduras",
                                    "HU" => "Hungary",
                                    "IS" => "Iceland",
                                    "IN" => "India",
                                    "ID" => "Indonesia",
                                    "IR" => "Iran",
                                    "IQ" => "Iraq",
                                    "IE" => "Ireland",
                                    "IL" => "Israel",
                                    "IT" => "Italy",
                                    "JM" => "Jamaica",
                                    "JP" => "Japan",
                                    "JO" => "Jordan",
                                    "KZ" => "Kazakhstan",
                                    "KE" => "Kenya",
                                    "KI" => "Kiribati",
                                    "KP" => "Korea, North",
                                    "KR" => "Korea, South",
                                    "KW" => "Kuwait",
                                    "KG" => "Kyrgyzstan",
                                    "LA" => "Laos",
                                    "LV" => "Latvia",
                                    "LB" => "Lebanon",
                                    "LS" => "Lesotho",
                                    "LR" => "Liberia",
                                    "LY" => "Libya",
                                    "LI" => "Liechtenstein",
                                    "LT" => "Lithuania",
                                    "LU" => "Luxembourg",
                                    "MG" => "Madagascar",
                                    "MW" => "Malawi",
                                    "MY" => "Malaysia",
                                    "MV" => "Maldives",
                                    "ML" => "Mali",
                                    "MT" => "Malta",
                                    "MH" => "Marshall Islands",
                                    "MR" => "Mauritania",
                                    "MU" => "Mauritius",
                                    "MX" => "Mexico",
                                    "FM" => "Micronesia",
                                    "MD" => "Moldova",
                                    "MC" => "Monaco",
                                    "MN" => "Mongolia",
                                    "ME" => "Montenegro",
                                    "MA" => "Morocco",
                                    "MZ" => "Mozambique",
                                    "MM" => "Myanmar",
                                    "NA" => "Namibia",
                                    "NR" => "Nauru",
                                    "NP" => "Nepal",
                                    "NL" => "Netherlands",
                                    "NZ" => "New Zealand",
                                    "NI" => "Nicaragua",
                                    "NE" => "Niger",
                                    "NG" => "Nigeria",
                                    "MK" => "North Macedonia",
                                    "NO" => "Norway",
                                    "OM" => "Oman",
                                    "PK" => "Pakistan",
                                    "PW" => "Palau",
                                    "PS" => "Palestine",
                                    "PA" => "Panama",
                                    "PG" => "Papua New Guinea",
                                    "PY" => "Paraguay",
                                    "PE" => "Peru",
                                    "PH" => "Philippines",
                                    "PL" => "Poland",
                                    "PT" => "Portugal",
                                    "QA" => "Qatar",
                                    "RO" => "Romania",
                                    "RU" => "Russia",
                                    "RW" => "Rwanda",
                                    "KN" => "Saint Kitts and Nevis",
                                    "LC" => "Saint Lucia",
                                    "VC" => "Saint Vincent and the Grenadines",
                                    "WS" => "Samoa",
                                    "SM" => "San Marino",
                                    "ST" => "Sao Tome and Principe",
                                    "SA" => "Saudi Arabia",
                                    "SN" => "Senegal",
                                    "RS" => "Serbia",
                                    "SC" => "Seychelles",
                                    "SL" => "Sierra Leone",
                                    "SG" => "Singapore",
                                    "SK" => "Slovakia",
                                    "SI" => "Slovenia",
                                    "SB" => "Solomon Islands",
                                    "SO" => "Somalia",
                                    "ZA" => "South Africa",
                                    "SS" => "South Sudan",
                                    "ES" => "Spain",
                                    "LK" => "Sri Lanka",
                                    "SD" => "Sudan",
                                    "SR" => "Suriname",
                                    "SE" => "Sweden",
                                    "CH" => "Switzerland",
                                    "SY" => "Syria",
                                    "TW" => "Taiwan",
                                    "TJ" => "Tajikistan",
                                    "TZ" => "Tanzania",
                                    "TH" => "Thailand",
                                    "TL" => "Timor-Leste",
                                    "TG" => "Togo",
                                    "TO" => "Tonga",
                                    "TT" => "Trinidad and Tobago",
                                    "TN" => "Tunisia",
                                    "TR" => "Turkey",
                                    "TM" => "Turkmenistan",
                                    "TV" => "Tuvalu",
                                    "UG" => "Uganda",
                                    "UA" => "Ukraine",
                                    "AE" => "United Arab Emirates",
                                    "GB" => "United Kingdom",
                                    "US" => "United States",
                                    "UY" => "Uruguay",
                                    "UZ" => "Uzbekistan",
                                    "VU" => "Vanuatu",
                                    "VA" => "Vatican City",
                                    "VE" => "Venezuela",
                                    "VN" => "Vietnam",
                                    "YE" => "Yemen",
                                    "ZM" => "Zambia",
                                    "ZW" => "Zimbabwe",
                                    "other" => "Other"
                                ];
                                foreach ($countries as $code => $label) {
                                    $selected = (isset($_POST['country']) && $_POST['country'] == $code) ? "selected" : "";
                                    echo "<option value=\"$code\" $selected>$label</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div>
                            <label for="interest" class="block text-sm font-medium text-gray-700 mb-2">I'm Interested In</label>
                            <select id="interest" name="interest" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                <option value="">Select your interest</option>
                                <option value="cultural" <?php if(isset($_POST['interest']) && $_POST['interest'] == 'cultural') echo 'selected'; ?>>Cultural Tours</option>
                                <option value="wildlife" <?php if(isset($_POST['interest']) && $_POST['interest'] == 'wildlife') echo 'selected'; ?>>Wildlife Safari</option>
                                <option value="beach" <?php if(isset($_POST['interest']) && $_POST['interest'] == 'beach') echo 'selected'; ?>>Beach & Coastal</option>
                                <option value="adventure" <?php if(isset($_POST['interest']) && $_POST['interest'] == 'adventure') echo 'selected'; ?>>Adventure Tours</option>
                                <option value="wellness" <?php if(isset($_POST['interest']) && $_POST['interest'] == 'wellness') echo 'selected'; ?>>Wellness Retreat</option>
                                <option value="custom" <?php if(isset($_POST['interest']) && $_POST['interest'] == 'custom') echo 'selected'; ?>>Custom Package</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="travel_date" class="block text-sm font-medium text-gray-700 mb-2">Preferred Travel Date</label>
                            <input type="date" id="travel_date" name="travel_date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent" value="<?php echo isset($_POST['travel_date']) ? htmlspecialchars($_POST['travel_date']) : ''; ?>">
                        </div>
                        
                        <div>
                            <label for="group_size" class="block text-sm font-medium text-gray-700 mb-2">Group Size</label>
                            <select id="group_size" name="group_size" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                <option value="">Select group size</option>
                                <option value="1" <?php if(isset($_POST['group_size']) && $_POST['group_size'] == '1') echo 'selected'; ?>>Solo Traveler</option>
                                <option value="2" <?php if(isset($_POST['group_size']) && $_POST['group_size'] == '2') echo 'selected'; ?>>2 People</option>
                                <option value="3-5" <?php if(isset($_POST['group_size']) && $_POST['group_size'] == '3-5') echo 'selected'; ?>>3-5 People</option>
                                <option value="6-10" <?php if(isset($_POST['group_size']) && $_POST['group_size'] == '6-10') echo 'selected'; ?>>6-10 People</option>
                                <option value="10+" <?php if(isset($_POST['group_size']) && $_POST['group_size'] == '10+') echo 'selected'; ?>>More than 10</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="message" class="block text-sm font-medium text-gray-700 mb-2">Message *</label>
                            <textarea id="message" name="message" rows="5" required placeholder="Tell us about your travel preferences, special requirements, or any questions you have..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : htmlspecialchars($initialMessage, ENT_QUOTES); ?></textarea>
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" id="newsletter" name="newsletter" class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded" <?php if(isset($_POST['newsletter'])) echo 'checked'; ?>>
                            <label for="newsletter" class="ml-2 block text-sm text-gray-700">
                                Subscribe to our newsletter for travel tips and special offers
                            </label>
                        </div>
                        
                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white py-3 px-6 rounded-lg font-semibold transition duration-300">
                            Send Message
                        </button>
                    </form>
                </div>
                
                <!-- Contact Information -->
                <div>
                    <h2 class="text-3xl font-bold text-gray-800 mb-6">Contact Information</h2>
                    
                    <div class="space-y-6">
                        <div class="flex items-start space-x-4">
                            <div class="bg-green-100 p-3 rounded-full">
                                <i class="fas fa-phone text-green-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-800 mb-1">Phone</h3>
                                <p><a class="text-green-700 hover:text-green-800 font-medium" href="tel:+94774809998">+94-774809998</a></p>
                                <p><a class="text-green-700 hover:text-green-800 font-medium" href="tel:+94716620407">+94716620407</a></p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-4">
                            <div class="bg-yellow-100 p-3 rounded-full">
                                <i class="fas fa-envelope text-yellow-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-800 mb-1">Email</h3>
                                <p><a class="text-green-700 hover:text-green-800 font-medium" href="mailto:hello@serendibpathways.com">hello@serendibpathways.com</a></p>
                            </div>
                        </div>
                        
                        <div class="flex items-start space-x-4">
                            <div class="bg-purple-100 p-3 rounded-full">
                                <i class="fas fa-clock text-purple-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-800 mb-1">Business Hours</h3>
                                <p class="text-gray-600">Monday – Friday: 8:00 AM – 8:00 PM<br>Saturday: 9:00 AM – 4:00 PM<br>Sunday: 9:00 AM – 4:00 PM</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Emergency Contact -->
                    <div class="mt-8 p-6 bg-red-50 rounded-lg border border-red-200">
                        <h3 class="font-semibold text-red-800 mb-4 flex items-center">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Emergency Services
                        </h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-red-700">General Emergency:</span>
                                <span class="text-red-800 font-semibold">118 or 119</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-red-700">Police Emergency:</span>
                                <span class="text-red-800 font-semibold">119</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-red-700">Medical Emergency:</span>
                                <span class="text-red-800 font-semibold">1990</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-red-700">Fire & Rescue:</span>
                                <span class="text-red-800 font-semibold">110</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-red-700">Tourist Police:</span>
                                <span class="text-red-800 font-semibold">+94 11 2421052</span>
                            </div>
                        </div>
                    </div>

                    <!-- Foreign Embassies -->
                    <div class="mt-6 p-6 bg-blue-50 rounded-lg border border-blue-200">
                        <h3 class="font-semibold text-blue-800 mb-4 flex items-center">
                            <i class="fas fa-flag mr-2"></i>
                            Foreign Embassies (Colombo)
                        </h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-blue-700">UK High Commission:</span>
                                <span class="text-blue-800 font-semibold">+94 11 5390639</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-blue-700">US Embassy:</span>
                                <span class="text-blue-800 font-semibold">+94 11 2498500</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-blue-700">Australian High Commission:</span>
                                <span class="text-blue-800 font-semibold">+94 11 2463200</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-blue-700">Indian High Commission:</span>
                                <span class="text-blue-800 font-semibold">+94 11 2421605</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-blue-700">Chinese Embassy:</span>
                                <span class="text-blue-800 font-semibold">+94 11 2688497</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-800 mb-4">Frequently Asked Questions</h2>
                <p class="text-gray-600">Quick answers to common questions</p>
            </div>
            
            <div class="max-w-3xl mx-auto space-y-4">
                <div class="border border-gray-200 rounded-lg">
                    <button class="w-full px-6 py-4 text-left font-semibold text-gray-800 hover:bg-gray-50 focus:outline-none" onclick="toggleFAQ(1)">
                        <div class="flex justify-between items-center">
                            <span>How far in advance should I book my tour?</span>
                            <i class="fas fa-chevron-down transform transition-transform" id="icon-1"></i>
                        </div>
                    </button>
                    <div class="hidden px-6 pb-4 text-gray-600" id="answer-1">
                        We recommend booking at least 2-4 weeks in advance, especially during peak season (December-March). However, we can often accommodate last-minute bookings based on availability.
                    </div>
                </div>
                
                <div class="border border-gray-200 rounded-lg">
                    <button class="w-full px-6 py-4 text-left font-semibold text-gray-800 hover:bg-gray-50 focus:outline-none" onclick="toggleFAQ(2)">
                        <div class="flex justify-between items-center">
                            <span>What is included in the tour packages?</span>
                            <i class="fas fa-chevron-down transform transition-transform" id="icon-2"></i>
                        </div>
                    </button>
                    <div class="hidden px-6 pb-4 text-gray-600" id="answer-2">
                        Our packages typically include accommodation, transportation, professional guide, entrance fees, and some meals. Specific inclusions vary by package - check individual package details or contact us for clarification.
                    </div>
                </div>
                
                <div class="border border-gray-200 rounded-lg">
                    <button class="w-full px-6 py-4 text-left font-semibold text-gray-800 hover:bg-gray-50 focus:outline-none" onclick="toggleFAQ(3)">
                        <div class="flex justify-between items-center">
                            <span>Do you offer customized tours?</span>
                            <i class="fas fa-chevron-down transform transition-transform" id="icon-3"></i>
                        </div>
                    </button>
                    <div class="hidden px-6 pb-4 text-gray-600" id="answer-3">
                        Yes! We specialize in creating customized tours based on your interests, budget, and schedule. Contact us with your preferences and we'll design a perfect itinerary for you.
                    </div>
                </div>
                
                <div class="border border-gray-200 rounded-lg">
                    <button class="w-full px-6 py-4 text-left font-semibold text-gray-800 hover:bg-gray-50 focus:outline-none" onclick="toggleFAQ(4)">
                        <div class="flex justify-between items-center">
                            <span>What is your cancellation policy?</span>
                            <i class="fas fa-chevron-down transform transition-transform" id="icon-4"></i>
                        </div>
                    </button>
                    <div class="hidden px-6 pb-4 text-gray-600" id="answer-4">
                        Cancellations made 30+ days before departure: Full refund minus processing fee. 15-29 days: 50% refund. Less than 15 days: No refund. We recommend travel insurance for added protection.
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        // FAQ toggle function
        function toggleFAQ(num) {
            const answer = document.getElementById('answer-' + num);
            const icon = document.getElementById('icon-' + num);
            
            if (answer.classList.contains('hidden')) {
                answer.classList.remove('hidden');
                icon.classList.add('rotate-180');
            } else {
                answer.classList.add('hidden');
                icon.classList.remove('rotate-180');
            }
        }
    </script>

<?php include 'includes/footer.php'; ?>
