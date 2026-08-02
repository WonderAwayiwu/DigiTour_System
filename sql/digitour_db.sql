-- DigiTour MySQL Database Schema & Complete Ghanaian Tourism Seed Data (All 16 Regions)

CREATE DATABASE IF NOT EXISTS `digitour_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `digitour_db`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `destination_images`, `hotel_images`, `inquiries`, `reviews`, `bookings`, `hotels`, `destinations`, `users`;
SET FOREIGN_KEY_CHECKS = 1;


-- 1. USERS TABLE
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `phone` VARCHAR(30) NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('tourist', 'admin') NOT NULL DEFAULT 'tourist',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. DESTINATIONS TABLE
CREATE TABLE IF NOT EXISTS `destinations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(150) NOT NULL,
  `region` VARCHAR(50) NOT NULL,
  `category` VARCHAR(50) NOT NULL,
  `description` LONGTEXT NOT NULL,
  `history` LONGTEXT NULL,
  `location_contact` VARCHAR(150) NULL,
  `image_main` VARCHAR(255) NULL,
  `image_2` VARCHAR(255) NULL,
  `image_3` VARCHAR(255) NULL,
  `is_featured` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. HOTELS TABLE (Linked 1-to-Many with Destinations)
CREATE TABLE IF NOT EXISTS `hotels` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `destination_id` INT NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `description` LONGTEXT NOT NULL,
  `price_per_night` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `room_capacity` INT NOT NULL DEFAULT 2,
  `contact_phone` VARCHAR(30) NULL,
  `contact_email` VARCHAR(100) NULL,
  `image` VARCHAR(255) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`destination_id`) REFERENCES `destinations`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. BOOKINGS TABLE
CREATE TABLE IF NOT EXISTS `bookings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `hotel_id` INT NOT NULL,
  `check_in_date` DATE NOT NULL,
  `check_out_date` DATE NOT NULL,
  `guests_count` INT NOT NULL DEFAULT 1,
  `total_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `status` ENUM('Pending', 'Confirmed', 'Cancelled') NOT NULL DEFAULT 'Pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`hotel_id`) REFERENCES `hotels`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. REVIEWS TABLE
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `destination_id` INT NULL,
  `hotel_id` INT NULL,
  `rating` INT NOT NULL DEFAULT 5,
  `comment` TEXT NOT NULL,
  `status` ENUM('Pending', 'Approved') NOT NULL DEFAULT 'Pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`destination_id`) REFERENCES `destinations`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`hotel_id`) REFERENCES `hotels`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. INQUIRIES TABLE
CREATE TABLE IF NOT EXISTS `inquiries` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NULL,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `subject` VARCHAR(150) NOT NULL,
  `message` TEXT NOT NULL,
  `admin_reply` TEXT NULL,
  `replied_at` TIMESTAMP NULL DEFAULT NULL,
  `status` ENUM('New', 'Replied', 'Resolved') DEFAULT 'New',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. DESTINATION IMAGES TABLE (Multi-image Gallery)
CREATE TABLE IF NOT EXISTS `destination_images` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `destination_id` INT NOT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `caption` VARCHAR(255) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`destination_id`) REFERENCES `destinations`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. HOTEL IMAGES TABLE (Multi-image Gallery)
CREATE TABLE IF NOT EXISTS `hotel_images` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `hotel_id` INT NOT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `caption` VARCHAR(255) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`hotel_id`) REFERENCES `hotels`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. PERFORMANCE INDEXES
CREATE INDEX `idx_hotels_dest_id` ON `hotels`(`destination_id`);
CREATE INDEX `idx_dest_featured_id` ON `destinations`(`is_featured`, `id`);
CREATE INDEX `idx_dest_region` ON `destinations`(`region`);
CREATE INDEX `idx_dest_category` ON `destinations`(`category`);
CREATE INDEX `idx_reviews_status` ON `reviews`(`status`);




-- SEED DATA - DEFAULT ACCOUNTS (Password for all: password123)
INSERT INTO `users` (`id`, `full_name`, `email`, `phone`, `password`, `role`) VALUES
(1, 'Ghana Tourism Admin', 'admin@digitour.gh', '+233240000000', '$2y$12$4FhHrK8Vu2ER2vhoKusNEusVg26ahRzp1KBe0Riri7yy1H/Wo8o66', 'admin'),
(2, 'Kwame Mensah', 'kwame@example.com', '+233244123456', '$2y$12$4FhHrK8Vu2ER2vhoKusNEusVg26ahRzp1KBe0Riri7yy1H/Wo8o66', 'tourist'),
(3, 'Adwoa Boateng', 'adwoa@example.com', '+233208987654', '$2y$12$4FhHrK8Vu2ER2vhoKusNEusVg26ahRzp1KBe0Riri7yy1H/Wo8o66', 'tourist');

-- SEED DATA - DESTINATIONS (ALL 16 REGIONS OF GHANA)

INSERT INTO `destinations` (`id`, `title`, `region`, `category`, `description`, `history`, `location_contact`, `image_main`, `is_featured`) VALUES
(1, 'Kwame Nkrumah Memorial Park', 'Greater Accra', 'History', '<h3>Geographic & Architectural Overview</h3>
<p>The Kwame Nkrumah Memorial Park and Mausoleum stands as a national monument of supreme historical and architectural prestige located in the heart of downtown Accra, Ghana. Occupying a prominent 5.4-acre landscaped estate directly opposite the Supreme Court of Ghana and adjacent to the Arts Centre, the park serves as the solemn final resting place of Osagyefo Dr. Kwame Nkrumah, the visionary scholar, statesman, and first President of the Republic of Ghana, along with his Egyptian wife, Fathia Nkrumah.</p>

<p>The central feature of the sanctuary is an imposing, 28-meter tall mausoleum designed by Ghanaian architect Don Arthur. Constructed entirely from pristine Italian marble imported from Carrara and capped with a dramatic bronze star, the mausoleum''s silhouette resembles a truncated sword inverted in traditional Ghanaian culture—symbolizing peace, finality, and posthumous reverence. Surrounding the tomb is a tranquil reflecting water basin flanked by bronze statues of traditional hornblowers casting jets of water toward the central tomb, creating an atmosphere of eternal serenity.</p>

<h3>Facilities, Museum Collections & Visitor Experience</h3>
<p>Following a multi-million dollar national rehabilitation in 2023, the park features a state-of-the-art museum, an interactive audio-visual gallery, a digital library, a VIP reception pavilion, and curated botanical gardens. The museum houses Dr. Nkrumah''s personal effects, including his academic robes from Lincoln and Pennsylvania Universities, his iconic wooden desk, historical speeches recorded on vintage reels, state gifts from world leaders during the Cold War era, and the famous metallic green 1960 Cadillac presidential car.</p>

<ul>
  <li><strong>Key Highlights:</strong> Italian Marble Mausoleum, Presidential Museum, Freedom Walkway, Executive Car Pavilion.</li>
  <li><strong>Visitor Access:</strong> Open daily from 8:30 AM to 6:00 PM with guided educational tours in English, French, and local languages.</li>
  <li><strong>Research Value:</strong> Essential repository for Pan-Africanism, African decolonization studies, and mid-20th-century political history.</li>
</ul>', '<h3>Historical Origins & The Dawn of Independence</h3>
<p>The site where the Kwame Nkrumah Memorial Park sits holds immense political significance in the story of African liberation. Historically, this expanse of land functioned as the former British Colonial Polo Grounds during the era of the Gold Coast colony. It was on these very grounds, on the midnight of March 5, 1957, that Dr. Kwame Nkrumah, flanked by his key political allies—Kojo Botsio, Komla Agbeli Gbedemah, Archie Casely-Hayford, Krobo Edusei, and N.A. Welbeck—stepped onto a raised platform before a crowd of over 100,000 freedom seekers to declare Ghana''s independence with the immortal words: <em>"At long last, the battle has ended! And thus Ghana, your beloved country is free forever!"</em></p>

<blockquote class="dt-quote">"Our independence is meaningless unless it is linked up with the total liberation of the African continent." — Dr. Kwame Nkrumah, March 6, 1957</blockquote>

<h3>The Overthrow, Exile, and Memorialization</h3>
<p>Following the CIA-backed military coup of February 24, 1966, Dr. Nkrumah was ousted from power while on a peace mission to Hanoi, Vietnam. He lived in exile in Conakry, Guinea, as co-president alongside President Ahmed Sékou Touré until his death from cancer in Bucharest, Romania, on April 27, 1972. Initially buried in Guinea and later in his ancestral hometown of Nkroful, his remains were finally transferred to Accra on July 1, 1992, during the reign of President Jerry John Rawlings, when the Memorial Park was officially commissioned.</p>

<p>Today, the park stands not only as a tomb but as a sacred shrine of global Pan-Africanism, attracting scholars, heads of state, members of the African diaspora, and international researchers who seek to understand the intellectual bedrock of African unity and anti-imperialist statecraft.</p>', 'Central Accra, opposite Supreme Court of Ghana', 'nkrumah_memorial.jpg', 1),
(2, 'Black Star Square (Independence Square)', 'Greater Accra', 'History', '<h3>Geographic Setting & Scale</h3>
<p>Black Star Square, officially known as Independence Square, is a vast, open-air national ceremonial parade ground located along the Gulf of Guinea coastline in Accra. Spanning several city blocks between Osu Castle and the Accra Sports Stadium, it ranks as the second-largest city square in the world after Tiananmen Square in Beijing, China, boasting a spectator seating capacity exceeding 30,000 people across three grand concrete stands.</p>

<p>The square is anchored by two monumental architectural structures: the <strong>Independence Arch</strong> to the south facing the Atlantic Ocean, and the <strong>Black Star Gate</strong> (also known as the Black Star Monument) positioned in the center of the main traffic roundabout. The five-pointed Black Star resting atop the monument represents the beacon of African freedom, inspired by Marcus Garvey''s Black Star Line shipping company.</p>

<h3>Public Utility & National Ceremonies</h3>
<p>Independence Square serves as the primary venue for Ghana''s most prestigious state gatherings, including the annual National Independence Day Parades on March 6th, presidential inaugurations, military brass band displays, international civic rallies, and national state funerals for departed leaders. At the center of the square stands the <em>Eternal Flame of African Liberation</em>, lit by Dr. Kwame Nkrumah in 1961 to symbolize the unyielding pursuit of freedom across the African continent.</p>', '<h3>Construction & Queen Elizabeth II''s Visit</h3>
<p>Commissioned by President Kwame Nkrumah in the late 1950s, Black Star Square was built specifically to host the grand state visit of Queen Elizabeth II of the United Kingdom in November 1961. Designed as a bold statement of African sovereignty, the architectural engineering of the grandstands combined mid-century modern brutalism with symbolic African motifs, declaring that the newly independent republic was capable of commanding international prestige on an equal footing with world powers.</p>

<h3>Symbolism & Political Evolution</h3>
<p>Throughout Ghana''s turbulent political history—spanning military transitions in 1966, 1972, 1978, 1979, and 1981, leading to the Fourth Republic in 1992—Black Star Square has remained the neutral, sacred heart of civic unity. The soldier monument standing guard opposite the square commemorates the Gold Coast veterans of World War I and II who fell during campaigns in Burma, East Africa, and Europe, linking Ghana''s martial history directly with its modern constitutional democracy.</p>', 'Coastal Osu/Ministries area, Accra', 'black_star_square.jpg', 1),
(3, 'Osu Castle (Christiansborg)', 'Greater Accra', 'History', '<h3>Coastal Architecture & Fortress Layout</h3>
<p>Osu Castle, historically known as Fort Christiansborg, is a massive 17th-century coastal fortress situated on a rocky promontory jutting into the Atlantic Ocean along the Osu beachfront in Accra. Built from imported European brick, mortar, and local granite, the fortress features thick curtain walls, bastion turrets, subterranean ammunition magazines, underground cisterns, and expansive executive suites overlooking the sea.</p>

<p>For over four centuries, the castle served dual roles: first as a heavily fortified trading lodge and slave dungeon during the transatlantic human trade, and later as the primary seat of executive government for British colonial governors and post-independence Ghanaian heads of state until government operations transitioned to Jubilee House in 2013.</p>

<h3>Modern Preservation & Public Museum Status</h3>
<p>Following its declaration as a UNESCO World Heritage site under the ''Castles and Forts of Ghana'' designation, Osu Castle has undergone heritage conservation to convert its historic quarters, presidential chambers, gardens, and dungeons into a public museum of political history and slave trade documentation.</p>', '<h3>Colonial Struggles & Ownership Transitions</h3>
<p>The initial trade post at Osu was established by the Swedes in 1652. However, in 1660, the Danish West India and Guinea Company under Governor Johan Ting seized the post and constructed a permanent stone fortress, naming it <strong>Christiansborg Castle</strong> after King Christian V of Denmark. Over the next two centuries, ownership flipped repeatedly: taken briefly by the Portuguese in 1679, reclaimed by Denmark, captured by the Akwamu warrior prince <em>Asamani</em> in 1693 through a legendary tactical ruse, and eventually sold to the British Crown in 1850 for £10,000.</p>

<blockquote class="dt-quote">"In 1693, Asamani of Akwamu disguised his warriors as traders, seized Christiansborg Castle from the Danish garrison, and ruled the fort as governor for a full year, trading with European ships under his own flag."</blockquote>

<h3>Transatlantic Trade & Post-Colonial Executive Seat</h3>
<p>During the 18th century, Christiansborg Castle was a central node in the transatlantic trade in enslaved Africans. Thousands of captives were confined in dark, damp oceanfront dungeons beneath the governor''s quarters before being marched through narrow sea gates onto slave ships bound for the Danish West Indies (St. Croix, St. Thomas, St. John) and the Americas. After independence, it hosted historic visits from international figures including Queen Elizabeth II, US President Bill Clinton, and US President Barack Obama.</p>', 'Osu Beachfront, Castle Dr, Accra', 'osu_castle.jpg', 0),
(4, 'Jamestown Lighthouse', 'Greater Accra', 'History & Culture', '<h3>Maritime Landmark & Coastal Topography</h3>
<p>The Jamestown Lighthouse is a striking 28-meter (92 ft) tall cylindrical stone and cast-iron tower painted in vivid red and white horizontal stripes, situated in the historic Ga Mashie district of Jamestown, Accra. Rising above the vibrant fishing beach of Jamestown Harbor and adjacent to Fort James, the lighthouse commands an unhindered panoramic view of the Gulf of Guinea, Ussher Fort, and the dense urban tapestry of old colonial Accra.</p>

<p>Equipped with a powerful rotating solar-powered optical lantern visible up to 16 nautical miles at sea, the structure continues to serve as an active navigational aid for local artisanal canoe fishermen and commercial ships navigating the Accra roadstead.</p>

<h3>Socio-Cultural Environment & Chale Wote Arts Festival</h3>
<p>Jamestown is the cultural cradle of the indigenous Ga people of Accra. The area surrounding the lighthouse comes alive annually during the internationally acclaimed <strong>Chale Wote Street Art Festival</strong>, where thousands of international artists, performers, historians, and filmmakers converge to transform the colonial streets into an open-air canvas of Afro-futurist art, music, and radical performance.</p>', '<h3>Colonial Maritime History</h3>
<p>The original Jamestown Lighthouse was erected by the British Imperial Administration in 1871 to guide commercial merchant vessels carrying cocoa, gold, and palm oil into the bustling port of British Accra. As maritime trade expanded rapidly in the early 20th century, the original timber-and-masonry tower was replaced in 1931 with the current reinforced brick and iron structure.</p>

<p>Jamestown itself grew around Fort James, built by the English Royal African Company in 1673. The surrounding neighborhood became the nexus of colonial commerce, boxing culture, and political agitation, breeding famous freedom fighters, scholars, and world-champion professional boxers such as Azumah Nelson and Ike Quartey.</p>', 'Bannerman Rd, Jamestown, Accra', 'jamestown_lighthouse.jpg', 0),
(5, 'National Museum of Ghana', 'Greater Accra', 'Culture & Heritage', '<h3>Architectural Design & Exhibition Galleries</h3>
<p>The National Museum of Ghana, situated on Barnes Road in Adabraka, Accra, is the largest and oldest heritage repository in the nation. Housed in a distinct mid-century dome building featuring a central skylight atrium, the museum comprises three main thematic galleries: <strong>Archaeology</strong>, <strong>Ethnography</strong>, and <strong>Art</strong>.</p>

<p>The exhibits take visitors on an immersive journey across millennia of West African human development, featuring Stone Age hand axes, ancient Kintampo culture artifacts, 14th-century brass weight systems, royal Asante kente textiles, traditional wooden stools, ritual beaded regalia, and contemporary Ghanaian fine art sculptures by master artists such as Kofi Antubam and El Anatsui.</p>

<h3>Outdoor Sculpture Garden & Educational Programs</h3>
<p>The museum grounds feature a tranquil shaded garden showcasing historic statues of political figures, traditional carved canoes, iron-smelting furnaces from Northern Ghana, and a children''s interactive discovery center hosting school excursions and academic lectures.</p>', '<h3>Establishment & Independence Commission</h3>
<p>The National Museum was officially opened on March 5, 1957, as a flagship cultural gift to the nation on the eve of Ghana''s independence. The opening ceremony was performed by Her Royal Highness the Duchess of Kent, representing the British Monarchy, alongside Prime Minister Kwame Nkrumah. The museum''s initial collection was formed by merging the historic museum of the Department of Archaeology at the University College of Ghana (Legon) with curated acquisitions from across West Africa.</p>

<p>Following a massive structural rehabilitation completed in May 2022 by President Nana Addo Dankwa Akufo-Addo, the museum re-opened with modernized climate-controlled display cases, expanded archival storage, and repatriated historical artifacts returned from European collections.</p>', 'Barnes Road, Adabraka, Accra', 'national_museum.jpg', 0),
(6, 'W.E.B. Du Bois Memorial Centre', 'Greater Accra', 'History', '<h3>Geographic & Architectural Overview</h3>
<p>An executive cultural compound housing the final residence, personal library, research archives, and mausoleum of the renowned African-American scholar, sociologist, and Pan-Africanist pioneer, Dr. William Edward Burghardt Du Bois.</p>
<p>Situated within the scenic environment of the <strong>Greater Accra</strong>, W.E.B. Du Bois Memorial Centre represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to W.E.B. Du Bois Memorial Centre can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Greater Accra</li>
  <li><strong>Category:</strong> History</li>
  <li><strong>Location / Access:</strong> Fifth Link Rd, Cantonments, Accra</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Dr. Du Bois moved to Accra in 1961 at the personal invitation of President Kwame Nkrumah to serve as Director-in-Chief of the Encyclopedia Africana. He spent his final years here surrounded by international scholars until his death on August 27, 1963, on the eve of the historic March on Washington.</p>
<p>Spanning multiple centuries of Ghanaian history, W.E.B. Du Bois Memorial Centre has served as a key economic, cultural, and spiritual anchor for communities in the Greater Accra. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving W.E.B. Du Bois Memorial Centre safeguards the living history and ecological legacy of the Greater Accra for national scholars and global visitors alike."</blockquote>', 'Fifth Link Rd, Cantonments, Accra', 'dubois_center.jpg', 0),
(7, 'Makola Market', 'Greater Accra', 'Culture & Heritage', '<h3>Geographic & Architectural Overview</h3>
<p>A vibrant, sprawling commercial powerhouse established in 1924, representing the financial epicentre of traditional retail trade in Accra, dominated by legendary Ga market queens trading textiles, gold, spices, and beads.</p>
<p>Situated within the scenic environment of the <strong>Greater Accra</strong>, Makola Market represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Makola Market can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Greater Accra</li>
  <li><strong>Category:</strong> Culture & Heritage</li>
  <li><strong>Location / Access:</strong> Kojo Thompson Rd, Central Accra</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Founded during the British colonial administration, Makola developed into a formidable institution of economic self-determination for Ghanaian women, surviving economic shifts, fires, and political evolutions.</p>
<p>Spanning multiple centuries of Ghanaian history, Makola Market has served as a key economic, cultural, and spiritual anchor for communities in the Greater Accra. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Makola Market safeguards the living history and ecological legacy of the Greater Accra for national scholars and global visitors alike."</blockquote>', 'Kojo Thompson Rd, Central Accra', 'makola_market.jpg', 0),
(8, 'Labadi Pleasure Beach', 'Greater Accra', 'Beach', '<h3>Geographic & Architectural Overview</h3>
<p>Ghana''s premier urban coastal destination, featuring expansive sandy oceanfront managed by the La Traditional Council, famous for weekend cultural performances, reggae music circles, horse riding, and beachfront gastronomy.</p>
<p>Situated within the scenic environment of the <strong>Greater Accra</strong>, Labadi Pleasure Beach represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Labadi Pleasure Beach can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Greater Accra</li>
  <li><strong>Category:</strong> Beach</li>
  <li><strong>Location / Access:</strong> La Rd, Accra</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Labadi Beach forms part of the ancestral fishing lands of the La Ga state. Historically a vital maritime commerce node, it evolved into Accra''s flagship municipal leisure resort during the late 20th century.</p>
<p>Spanning multiple centuries of Ghanaian history, Labadi Pleasure Beach has served as a key economic, cultural, and spiritual anchor for communities in the Greater Accra. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Labadi Pleasure Beach safeguards the living history and ecological legacy of the Greater Accra for national scholars and global visitors alike."</blockquote>', 'La Rd, Accra', 'labadi_beach.jpg', 1),
(9, 'Bojo Beach', 'Greater Accra', 'Beach', '<h3>Geographic & Architectural Overview</h3>
<p>An eco-friendly barrier island beach near Bortianor, where visitors cross a pristine saltwater lagoon in traditional hand-paddled wooden canoes to reach an uncrowded Atlantic sandbar.</p>
<p>Situated within the scenic environment of the <strong>Greater Accra</strong>, Bojo Beach represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Bojo Beach can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Greater Accra</li>
  <li><strong>Category:</strong> Beach</li>
  <li><strong>Location / Access:</strong> Bortianor, West Accra</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Developed as a model private-community eco-tourism reserve, Bojo Beach preserves coastal wetlands while offering a peaceful natural sanctuary away from urban Accra.</p>
<p>Spanning multiple centuries of Ghanaian history, Bojo Beach has served as a key economic, cultural, and spiritual anchor for communities in the Greater Accra. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Bojo Beach safeguards the living history and ecological legacy of the Greater Accra for national scholars and global visitors alike."</blockquote>', 'Bortianor, West Accra', 'bojo_beach.jpg', 0),
(10, 'Kokrobite Beach', 'Greater Accra', 'Beach', '<h3>Geographic & Architectural Overview</h3>
<p>A world-renowned bohemian fishing village and surf beach, celebrated for its global music academy, artisanal canoe harbor, vibrant nightlife, and backpacker eco-lodges.</p>
<p>Situated within the scenic environment of the <strong>Greater Accra</strong>, Kokrobite Beach represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Kokrobite Beach can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Greater Accra</li>
  <li><strong>Category:</strong> Beach</li>
  <li><strong>Location / Access:</strong> Kokrobite (30 km west of Accra)</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Kokrobite gained international renown when Ghanaian master drummer Mustapha Tettey Addy established the Academy of African Music and Art (AAMA) here in 1988, turning it into an international center for African percussion.</p>
<p>Spanning multiple centuries of Ghanaian history, Kokrobite Beach has served as a key economic, cultural, and spiritual anchor for communities in the Greater Accra. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Kokrobite Beach safeguards the living history and ecological legacy of the Greater Accra for national scholars and global visitors alike."</blockquote>', 'Kokrobite (30 km west of Accra)', 'kokrobite_beach.jpg', 0),
(11, 'Achimota Forest and Accra Zoo', 'Greater Accra', 'Nature & Wildlife', '<h3>Geographic & Architectural Overview</h3>
<p>A vital 360-hectare urban forestry reserve and wildlife sanctuary providing a green ecological lung for Accra, harboring native primates, avians, and relocated lions.</p>
<p>Situated within the scenic environment of the <strong>Greater Accra</strong>, Achimota Forest and Accra Zoo represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Achimota Forest and Accra Zoo can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Greater Accra</li>
  <li><strong>Category:</strong> Nature & Wildlife</li>
  <li><strong>Location / Access:</strong> Off Gimpa Road, Achimota, Accra</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Gazetted as a protected forest reserve in 1930 under British Governor Sir Ransford Slater to shield Accra''s micro-climate and water catchments.</p>
<p>Spanning multiple centuries of Ghanaian history, Achimota Forest and Accra Zoo has served as a key economic, cultural, and spiritual anchor for communities in the Greater Accra. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Achimota Forest and Accra Zoo safeguards the living history and ecological legacy of the Greater Accra for national scholars and global visitors alike."</blockquote>', 'Off Gimpa Road, Achimota, Accra', 'achimota_forest.jpg', 0),
(12, 'Legon Botanical Gardens', 'Greater Accra', 'Nature & Wildlife', '<h3>Geographic & Architectural Overview</h3>
<p>A 50-hectare biodiversity haven boasting high-rope canopy obstacle courses, canoeing lakes, arboretum trails, and medicinal plant reserves.</p>
<p>Situated within the scenic environment of the <strong>Greater Accra</strong>, Legon Botanical Gardens represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Legon Botanical Gardens can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Greater Accra</li>
  <li><strong>Category:</strong> Nature & Wildlife</li>
  <li><strong>Location / Access:</strong> Atomic-Haatso Road, Legon, Accra</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Established in 1950 jointly by the University of Ghana and international botanical researchers to preserve indigenous West African flora.</p>
<p>Spanning multiple centuries of Ghanaian history, Legon Botanical Gardens has served as a key economic, cultural, and spiritual anchor for communities in the Greater Accra. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Legon Botanical Gardens safeguards the living history and ecological legacy of the Greater Accra for national scholars and global visitors alike."</blockquote>', 'Atomic-Haatso Road, Legon, Accra', 'legon_gardens.jpg', 0),
(13, 'Shai Hills Resource Reserve', 'Greater Accra', 'Nature & Wildlife', '<h3>Geographic & Architectural Overview</h3>
<p>A 51-square-kilometer fenced savanna reserve protecting troops of olive baboons, ostriches, antelopes, zebras, and inselberg rock caves.</p>
<p>Situated within the scenic environment of the <strong>Greater Accra</strong>, Shai Hills Resource Reserve represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Shai Hills Resource Reserve can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Greater Accra</li>
  <li><strong>Category:</strong> Nature & Wildlife</li>
  <li><strong>Location / Access:</strong> Doryumu, Tema-Akosombo Highway</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>The inselbergs served as the ancestral fortified homeland of the Dangme Shai ethnic group until their expulsion by British colonial authorities in 1892.</p>
<p>Spanning multiple centuries of Ghanaian history, Shai Hills Resource Reserve has served as a key economic, cultural, and spiritual anchor for communities in the Greater Accra. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Shai Hills Resource Reserve safeguards the living history and ecological legacy of the Greater Accra for national scholars and global visitors alike."</blockquote>', 'Doryumu, Tema-Akosombo Highway', 'shai_hills.jpg', 0),
(14, 'Treasure Island', 'Greater Accra', 'Nature & Waterways', '<h3>Geographic & Architectural Overview</h3>
<p>A luxury island aquatic resort situated at the dramatic meeting point where the freshwater Volta River meets the Atlantic Ocean.</p>
<p>Situated within the scenic environment of the <strong>Greater Accra</strong>, Treasure Island represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Treasure Island can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Greater Accra</li>
  <li><strong>Category:</strong> Nature & Waterways</li>
  <li><strong>Location / Access:</strong> Ada Foah Estuary, Ada</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Ada Foah served as a 19th-century trade post for Danish and British merchants trading palm oil, salt, and gold along the Volta River network.</p>
<p>Spanning multiple centuries of Ghanaian history, Treasure Island has served as a key economic, cultural, and spiritual anchor for communities in the Greater Accra. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Treasure Island safeguards the living history and ecological legacy of the Greater Accra for national scholars and global visitors alike."</blockquote>', 'Ada Foah Estuary, Ada', 'treasure_island.jpg', 1),
(15, 'Fort Great George', 'Greater Accra', 'History', '<h3>Geographic & Architectural Overview</h3>
<p>Colonial military fortification ruins built atop James Town elevation, offering strategic command of maritime approach routes.</p>
<p>Situated within the scenic environment of the <strong>Greater Accra</strong>, Fort Great George represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Fort Great George can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Greater Accra</li>
  <li><strong>Category:</strong> History</li>
  <li><strong>Location / Access:</strong> James Town Hills, Accra</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Erected by the British military during colonial expansions to bolster defense lines surrounding James Town and Ussher Fort.</p>
<p>Spanning multiple centuries of Ghanaian history, Fort Great George has served as a key economic, cultural, and spiritual anchor for communities in the Greater Accra. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Fort Great George safeguards the living history and ecological legacy of the Greater Accra for national scholars and global visitors alike."</blockquote>', 'James Town Hills, Accra', 'fort_great_george.jpg', 0),
(16, 'Teshie Saneba Bead Factory', 'Greater Accra', 'Culture & Heritage', '<h3>Geographic & Architectural Overview</h3>
<p>Artisanal glass bead manufacturing workshop preserving centuries-old techniques of crushing, molding, kiln-firing, and painting recycled glass beads.</p>
<p>Situated within the scenic environment of the <strong>Greater Accra</strong>, Teshie Saneba Bead Factory represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Teshie Saneba Bead Factory can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Greater Accra</li>
  <li><strong>Category:</strong> Culture & Heritage</li>
  <li><strong>Location / Access:</strong> Nungua Market, Teshie, Accra</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Bead-making forms a vital spiritual component of Ga and Krobo initiation rites (such as Dipo), functioning as currency and prestige markers since pre-colonial times.</p>
<p>Spanning multiple centuries of Ghanaian history, Teshie Saneba Bead Factory has served as a key economic, cultural, and spiritual anchor for communities in the Greater Accra. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Teshie Saneba Bead Factory safeguards the living history and ecological legacy of the Greater Accra for national scholars and global visitors alike."</blockquote>', 'Nungua Market, Teshie, Accra', 'bead_factory.jpg', 0),
(17, 'Kane Kwei Carpentry Workshop', 'Greater Accra', 'Culture & Heritage', '<h3>Geographic & Architectural Overview</h3>
<p>World-famous workshop where master craftsmen sculpt custom ''fantasy coffins'' (abebuu adekai) shaped as cars, fish, cocoa pods, and airplanes.</p>
<p>Situated within the scenic environment of the <strong>Greater Accra</strong>, Kane Kwei Carpentry Workshop represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Kane Kwei Carpentry Workshop can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Greater Accra</li>
  <li><strong>Category:</strong> Culture & Heritage</li>
  <li><strong>Location / Access:</strong> Teshie Coastal Road, Accra</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Pioneered by Seth Kane Kwei in the 1950s, this art form transforms funeral rites into a celebration of the deceased''s profession, gaining global fame in museums like the Centre Pompidou and Smithsonian.</p>
<p>Spanning multiple centuries of Ghanaian history, Kane Kwei Carpentry Workshop has served as a key economic, cultural, and spiritual anchor for communities in the Greater Accra. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Kane Kwei Carpentry Workshop safeguards the living history and ecological legacy of the Greater Accra for national scholars and global visitors alike."</blockquote>', 'Teshie Coastal Road, Accra', 'kane_kwei.jpg', 0),
(18, 'Manhyia Palace Museum', 'Ashanti Region', 'Culture & Heritage', '<h3>Geographic & Architectural Overview</h3>
<p>The administrative center of the Asante Kingdom and former royal residence, housing royal regalia, war maps, and court antiquities.</p>
<p>Situated within the scenic environment of the <strong>Ashanti Region</strong>, Manhyia Palace Museum represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Manhyia Palace Museum can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Ashanti Region</li>
  <li><strong>Category:</strong> Culture & Heritage</li>
  <li><strong>Location / Access:</strong> Kumasi, Ashanti Region</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Built by the British government in 1925 as an official residence for King Prempeh I upon his return from a 28-year colonial exile in the Seychelles Islands.</p>
<p>Spanning multiple centuries of Ghanaian history, Manhyia Palace Museum has served as a key economic, cultural, and spiritual anchor for communities in the Ashanti Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Manhyia Palace Museum safeguards the living history and ecological legacy of the Ashanti Region for national scholars and global visitors alike."</blockquote>', 'Kumasi, Ashanti Region', 'manhyia_palace.jpg', 1),
(19, 'Lake Bosomtwe', 'Ashanti Region', 'Nature & Waterways', '<h3>Geographic & Architectural Overview</h3>
<p>Ghana''s only natural lake, formed within a 10.5-km wide meteorite impact crater formed over 1.07 million years ago, surrounded by rainforest hills.</p>
<p>Situated within the scenic environment of the <strong>Ashanti Region</strong>, Lake Bosomtwe represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Lake Bosomtwe can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Ashanti Region</li>
  <li><strong>Category:</strong> Nature & Waterways</li>
  <li><strong>Location / Access:</strong> Kuntanase District (30 km from Kumasi)</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Revered by the Asante people as a sacred body of water where the souls of the deceased come to say goodbye to the god Twi before journeying to the afterlife.</p>
<p>Spanning multiple centuries of Ghanaian history, Lake Bosomtwe has served as a key economic, cultural, and spiritual anchor for communities in the Ashanti Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Lake Bosomtwe safeguards the living history and ecological legacy of the Ashanti Region for national scholars and global visitors alike."</blockquote>', 'Kuntanase District (30 km from Kumasi)', 'lake_bosomtwe.jpg', 1),
(20, 'Kejetia Market', 'Ashanti Region', 'Culture & Heritage', '<h3>Geographic & Architectural Overview</h3>
<p>West Africa''s largest open-air covered market complex, featuring over 11,000 stores trading textiles, gold jewelry, spices, and indigenous craft items.</p>
<p>Situated within the scenic environment of the <strong>Ashanti Region</strong>, Kejetia Market represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Kejetia Market can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Ashanti Region</li>
  <li><strong>Category:</strong> Culture & Heritage</li>
  <li><strong>Location / Access:</strong> Downtown Kumasi</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Historical commercial heart of the Asante Empire, Kejetia has served as the regional trade crossroads connecting coastal ports to trans-Saharan caravan routes for centuries.</p>
<p>Spanning multiple centuries of Ghanaian history, Kejetia Market has served as a key economic, cultural, and spiritual anchor for communities in the Ashanti Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Kejetia Market safeguards the living history and ecological legacy of the Ashanti Region for national scholars and global visitors alike."</blockquote>', 'Downtown Kumasi', 'kejetia_market.jpg', 0),
(21, 'Okomfo Anokye Sword Site', 'Ashanti Region', 'History & Culture', '<h3>Geographic & Architectural Overview</h3>
<p>A sacred shrine holding a legendary steel sword pushed deep into the earth by high priest Okomfo Anokye over 300 years ago, which remains unmovable by human force or heavy machinery.</p>
<p>Situated within the scenic environment of the <strong>Ashanti Region</strong>, Okomfo Anokye Sword Site represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Okomfo Anokye Sword Site can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Ashanti Region</li>
  <li><strong>Category:</strong> History & Culture</li>
  <li><strong>Location / Access:</strong> KATH Grounds, Kumasi</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Inserted into the soil around 1695 to symbolize the eternal unity of the Ashanti Kingdom and the descent of the Golden Stool (Sika Dwa) from the heavens.</p>
<p>Spanning multiple centuries of Ghanaian history, Okomfo Anokye Sword Site has served as a key economic, cultural, and spiritual anchor for communities in the Ashanti Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Okomfo Anokye Sword Site safeguards the living history and ecological legacy of the Ashanti Region for national scholars and global visitors alike."</blockquote>', 'KATH Grounds, Kumasi', 'okomfo_anokye_sword.jpg', 0),
(22, 'Bonwire Kente Village', 'Ashanti Region', 'Culture & Heritage', '<h3>Geographic & Architectural Overview</h3>
<p>The historic weaving epicenter where master weavers construct intricate, multi-colored royal Kente cloth patterns using traditional wooden foot looms.</p>
<p>Situated within the scenic environment of the <strong>Ashanti Region</strong>, Bonwire Kente Village represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Bonwire Kente Village can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Ashanti Region</li>
  <li><strong>Category:</strong> Culture & Heritage</li>
  <li><strong>Location / Access:</strong> Ejisu-Juaben District</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Legend holds that two brothers, Kuragu and Ameyaw, learned the art of weaving by observing a spider (Ananse) weaving its web in the Bonwire forest during the reign of King Osei Tutu I.</p>
<p>Spanning multiple centuries of Ghanaian history, Bonwire Kente Village has served as a key economic, cultural, and spiritual anchor for communities in the Ashanti Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Bonwire Kente Village safeguards the living history and ecological legacy of the Ashanti Region for national scholars and global visitors alike."</blockquote>', 'Ejisu-Juaben District', 'bonwire_kente.jpg', 1),
(23, 'Adanwomase Kente Village', 'Ashanti Region', 'Culture & Heritage', '<h3>Geographic & Architectural Overview</h3>
<p>Royal weaver community offering interactive weaving demonstrations, cocoa farm eco-tours, and folklore storytelling.</p>
<p>Situated within the scenic environment of the <strong>Ashanti Region</strong>, Adanwomase Kente Village represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Adanwomase Kente Village can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Ashanti Region</li>
  <li><strong>Category:</strong> Culture & Heritage</li>
  <li><strong>Location / Access:</strong> Ejisu District</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Historically appointed as the primary supplier of royal ceremonial cloths to the Asantehene''s court at Manhyia Palace.</p>
<p>Spanning multiple centuries of Ghanaian history, Adanwomase Kente Village has served as a key economic, cultural, and spiritual anchor for communities in the Ashanti Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Adanwomase Kente Village safeguards the living history and ecological legacy of the Ashanti Region for national scholars and global visitors alike."</blockquote>', 'Ejisu District', 'adanwomase.jpg', 0),
(24, 'Ntonso Adinkra Village', 'Ashanti Region', 'Culture & Heritage', '<h3>Geographic & Architectural Overview</h3>
<p>The production hub for traditional Adinkra textile printing using hand-carved calabash stamps and natural dye extracted from boiled Badie tree bark.</p>
<p>Situated within the scenic environment of the <strong>Ashanti Region</strong>, Ntonso Adinkra Village represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Ntonso Adinkra Village can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Ashanti Region</li>
  <li><strong>Category:</strong> Culture & Heritage</li>
  <li><strong>Location / Access:</strong> Mampong Road, North of Kumasi</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Originating in the early 19th century following the Gyaman war, Adinkra symbols convey ancient Akan philosophy, proverbs, and spiritual wisdom.</p>
<p>Spanning multiple centuries of Ghanaian history, Ntonso Adinkra Village has served as a key economic, cultural, and spiritual anchor for communities in the Ashanti Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Ntonso Adinkra Village safeguards the living history and ecological legacy of the Ashanti Region for national scholars and global visitors alike."</blockquote>', 'Mampong Road, North of Kumasi', 'ntonso_adinkra.jpg', 0),
(25, 'Ahwiaa Woodcarving Village', 'Ashanti Region', 'Culture & Heritage', '<h3>Geographic & Architectural Overview</h3>
<p>A linear artisan settlement specialized in hand-carving royal Asante wooden stools, Akua''ba fertility dolls, game boards (Oware), and masks.</p>
<p>Situated within the scenic environment of the <strong>Ashanti Region</strong>, Ahwiaa Woodcarving Village represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Ahwiaa Woodcarving Village can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Ashanti Region</li>
  <li><strong>Category:</strong> Culture & Heritage</li>
  <li><strong>Location / Access:</strong> Kumasi-Mampong Road</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Established as a royal woodcarvers guild during the expansion of the Asante Kingdom under Asantehene Opoku Ware I in the 18th century.</p>
<p>Spanning multiple centuries of Ghanaian history, Ahwiaa Woodcarving Village has served as a key economic, cultural, and spiritual anchor for communities in the Ashanti Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Ahwiaa Woodcarving Village safeguards the living history and ecological legacy of the Ashanti Region for national scholars and global visitors alike."</blockquote>', 'Kumasi-Mampong Road', 'ahwiaa_carving.jpg', 0),
(26, 'Bobiri Forest Reserve & Butterfly Sanctuary', 'Ashanti Region', 'Nature & Wildlife', '<h3>Geographic & Architectural Overview</h3>
<p>An old-growth tropical rainforest reserve home to over 400 distinct butterfly species, giant timber trees, and lush walking trails.</p>
<p>Situated within the scenic environment of the <strong>Ashanti Region</strong>, Bobiri Forest Reserve & Butterfly Sanctuary represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Bobiri Forest Reserve & Butterfly Sanctuary can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Ashanti Region</li>
  <li><strong>Category:</strong> Nature & Wildlife</li>
  <li><strong>Location / Access:</strong> Kubease, off Kumasi-Accra highway</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Created in 1939 as an eco-forestry conservation site to study biodiversity and protect West African rain forest flora.</p>
<p>Spanning multiple centuries of Ghanaian history, Bobiri Forest Reserve & Butterfly Sanctuary has served as a key economic, cultural, and spiritual anchor for communities in the Ashanti Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Bobiri Forest Reserve & Butterfly Sanctuary safeguards the living history and ecological legacy of the Ashanti Region for national scholars and global visitors alike."</blockquote>', 'Kubease, off Kumasi-Accra highway', 'bobiri_forest.jpg', 0),
(27, 'Kumasi Zoological Gardens', 'Ashanti Region', 'Nature & Wildlife', '<h3>Geographic & Architectural Overview</h3>
<p>A vintage 1.5-hectare urban zoo conserving regional West African fauna including baboons, chimps, lions, pythons, and native bird species.</p>
<p>Situated within the scenic environment of the <strong>Ashanti Region</strong>, Kumasi Zoological Gardens represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Kumasi Zoological Gardens can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Ashanti Region</li>
  <li><strong>Category:</strong> Nature & Wildlife</li>
  <li><strong>Location / Access:</strong> Near Kejetia, Kumasi</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Opened in 1957 to educate the public on wildlife conservation and preserve indigenous animals endangered by urban expansion.</p>
<p>Spanning multiple centuries of Ghanaian history, Kumasi Zoological Gardens has served as a key economic, cultural, and spiritual anchor for communities in the Ashanti Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Kumasi Zoological Gardens safeguards the living history and ecological legacy of the Ashanti Region for national scholars and global visitors alike."</blockquote>', 'Near Kejetia, Kumasi', 'kumasi_zoo.jpg', 0),
(28, 'Pankrono Pottery Village', 'Ashanti Region', 'Culture & Heritage', '<h3>Geographic & Architectural Overview</h3>
<p>Artisanal clay village specialized in ancestral hand-molded earthenware, water storage pots, and decorative terracotta.</p>
<p>Situated within the scenic environment of the <strong>Ashanti Region</strong>, Pankrono Pottery Village represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Pankrono Pottery Village can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Ashanti Region</li>
  <li><strong>Category:</strong> Culture & Heritage</li>
  <li><strong>Location / Access:</strong> 8 km North of Kumasi</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Centuries-old potters community extracting natural clay from local riverbanks using techniques passed down through matriarchal lines.</p>
<p>Spanning multiple centuries of Ghanaian history, Pankrono Pottery Village has served as a key economic, cultural, and spiritual anchor for communities in the Ashanti Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Pankrono Pottery Village safeguards the living history and ecological legacy of the Ashanti Region for national scholars and global visitors alike."</blockquote>', '8 km North of Kumasi', 'pankrono_pottery.jpg', 0),
(29, 'Owabi Wildlife Sanctuary', 'Ashanti Region', 'Nature & Wildlife', '<h3>Geographic & Architectural Overview</h3>
<p>Ghana''s only inland Ramsar wetland site, enveloping a tranquil water reservoir surrounded by secondary forest and bird habitats.</p>
<p>Situated within the scenic environment of the <strong>Ashanti Region</strong>, Owabi Wildlife Sanctuary represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Owabi Wildlife Sanctuary can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Ashanti Region</li>
  <li><strong>Category:</strong> Nature & Wildlife</li>
  <li><strong>Location / Access:</strong> Owabi, near Kumasi</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Established in 1971 to protect Kumasi''s municipal water catchment area while preserving over 160 migratory and resident bird species.</p>
<p>Spanning multiple centuries of Ghanaian history, Owabi Wildlife Sanctuary has served as a key economic, cultural, and spiritual anchor for communities in the Ashanti Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Owabi Wildlife Sanctuary safeguards the living history and ecological legacy of the Ashanti Region for national scholars and global visitors alike."</blockquote>', 'Owabi, near Kumasi', 'owabi_sanctuary.jpg', 0),
(30, 'Kumasi Military Museum', 'Ashanti Region', 'History', '<h3>Geographic & Architectural Overview</h3>
<p>Historic British fort housing weapons, uniforms, medals, and photographs detailing Gold Coast military engagements and WWII campaigns.</p>
<p>Situated within the scenic environment of the <strong>Ashanti Region</strong>, Kumasi Military Museum represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Kumasi Military Museum can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Ashanti Region</li>
  <li><strong>Category:</strong> History</li>
  <li><strong>Location / Access:</strong> Fort St. George Complex, Kumasi</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Originally constructed by the British colonial force in 1896 following the exile of King Prempeh I and later besieged during the 1900 Yaa Asantewaa War.</p>
<p>Spanning multiple centuries of Ghanaian history, Kumasi Military Museum has served as a key economic, cultural, and spiritual anchor for communities in the Ashanti Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Kumasi Military Museum safeguards the living history and ecological legacy of the Ashanti Region for national scholars and global visitors alike."</blockquote>', 'Fort St. George Complex, Kumasi', 'kumasi_military_museum.jpg', 0),
(31, 'Obuasi Gold Mine (Surface Tours)', 'Ashanti Region', 'History', '<h3>Geographic & Architectural Overview</h3>
<p>Educational surface tours of one of the richest gold reserves on Earth, showcasing over a century of industrial underground mining engineering.</p>
<p>Situated within the scenic environment of the <strong>Ashanti Region</strong>, Obuasi Gold Mine (Surface Tours) represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Obuasi Gold Mine (Surface Tours) can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Ashanti Region</li>
  <li><strong>Category:</strong> History</li>
  <li><strong>Location / Access:</strong> Obuasi Town</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Commercial extraction began in 1897 under the Ashanti Goldfields Corporation, driving West African industrialization and global gold trade.</p>
<p>Spanning multiple centuries of Ghanaian history, Obuasi Gold Mine (Surface Tours) has served as a key economic, cultural, and spiritual anchor for communities in the Ashanti Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Obuasi Gold Mine (Surface Tours) safeguards the living history and ecological legacy of the Ashanti Region for national scholars and global visitors alike."</blockquote>', 'Obuasi Town', 'obuasi_gold_mine.jpg', 0),
(32, 'Kakum National Park Canopy Walkway', 'Central Region', 'Nature & Wildlife', '<h3>Geographic & Architectural Overview</h3>
<p>A 375-square-kilometer tropical rainforest sanctuary featuring a world-famous 350-meter canopy walkway suspended 40 meters above the forest floor.</p>
<p>Situated within the scenic environment of the <strong>Central Region</strong>, Kakum National Park Canopy Walkway represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Kakum National Park Canopy Walkway can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Central Region</li>
  <li><strong>Category:</strong> Nature & Wildlife</li>
  <li><strong>Location / Access:</strong> Abrafo Town, near Jukwa</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Gazetted as a forest reserve in 1932 and upgraded to a national park in 1992 through local chieftaincy initiatives to preserve wet evergreen rainforest fauna.</p>
<p>Spanning multiple centuries of Ghanaian history, Kakum National Park Canopy Walkway has served as a key economic, cultural, and spiritual anchor for communities in the Central Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Kakum National Park Canopy Walkway safeguards the living history and ecological legacy of the Central Region for national scholars and global visitors alike."</blockquote>', 'Abrafo Town, near Jukwa', 'kakum.jpg', 1),
(33, 'Cape Coast Castle', 'Central Region', 'History', '<h3>Geographic & Architectural Overview</h3>
<p>A UNESCO World Heritage fortress standing on a rocky ocean promontory, containing dark slave dungeons, cannon bastions, and the historic ''Door of No Return''.</p>
<p>Situated within the scenic environment of the <strong>Central Region</strong>, Cape Coast Castle represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Cape Coast Castle can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Central Region</li>
  <li><strong>Category:</strong> History</li>
  <li><strong>Location / Access:</strong> Victoria Road, Cape Coast Shoreline</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Originally built by the Swedes in 1653 as Fort Carolusborg, seized by the British in 1665, becoming the headquarters of British colonial slave trade on the Gold Coast.</p>
<p>Spanning multiple centuries of Ghanaian history, Cape Coast Castle has served as a key economic, cultural, and spiritual anchor for communities in the Central Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Cape Coast Castle safeguards the living history and ecological legacy of the Central Region for national scholars and global visitors alike."</blockquote>', 'Victoria Road, Cape Coast Shoreline', 'cape_coast_castle.jpg', 1),
(34, 'Elmina Castle (St. George''s)', 'Central Region', 'History', '<h3>Geographic & Architectural Overview</h3>
<p>The oldest European-built structure in sub-Saharan Africa, constructed by Portuguese traders in 1482, featuring courtyard dungeons and luxury governor quarters.</p>
<p>Situated within the scenic environment of the <strong>Central Region</strong>, Elmina Castle (St. George''s) represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Elmina Castle (St. George''s) can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Central Region</li>
  <li><strong>Category:</strong> History</li>
  <li><strong>Location / Access:</strong> Elmina Shoreline</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Erected under Portuguese commander Diogo d''Azambuja, captured by the Dutch West India Company in 1637, serving as a primary embarkation point for enslaved Africans.</p>
<p>Spanning multiple centuries of Ghanaian history, Elmina Castle (St. George''s) has served as a key economic, cultural, and spiritual anchor for communities in the Central Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Elmina Castle (St. George''s) safeguards the living history and ecological legacy of the Central Region for national scholars and global visitors alike."</blockquote>', 'Elmina Shoreline', 'elmina_castle.jpg', 1),
(35, 'Fort Jago (Coenraadsburg)', 'Central Region', 'History', '<h3>Geographic & Architectural Overview</h3>
<p>A hilltop military fort overlooking Elmina Castle, constructed by the Dutch to secure strategic high ground against enemy artillery attacks.</p>
<p>Situated within the scenic environment of the <strong>Central Region</strong>, Fort Jago (Coenraadsburg) represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Fort Jago (Coenraadsburg) can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Central Region</li>
  <li><strong>Category:</strong> History</li>
  <li><strong>Location / Access:</strong> Elmina Hilltops</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Built in 1655 on St. Jago Hill where the Dutch launched their successful siege against the Portuguese garrison at Elmina in 1637.</p>
<p>Spanning multiple centuries of Ghanaian history, Fort Jago (Coenraadsburg) has served as a key economic, cultural, and spiritual anchor for communities in the Central Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Fort Jago (Coenraadsburg) safeguards the living history and ecological legacy of the Central Region for national scholars and global visitors alike."</blockquote>', 'Elmina Hilltops', 'fort_jago.jpg', 0),
(36, 'Assin Manso Slave River Site', 'Central Region', 'History', '<h3>Geographic & Architectural Overview</h3>
<p>A sacred memorial river site (Donkor Nsuo) where captured Africans were given their final bath before being marched in chains to coastal dungeon castles.</p>
<p>Situated within the scenic environment of the <strong>Central Region</strong>, Assin Manso Slave River Site represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Assin Manso Slave River Site can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Central Region</li>
  <li><strong>Category:</strong> History</li>
  <li><strong>Location / Access:</strong> Assin Manso, Cape Coast Highway</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Served as the central transit depot for slave caravans traveling south along northern trade routes during the 18th and 19th centuries.</p>
<p>Spanning multiple centuries of Ghanaian history, Assin Manso Slave River Site has served as a key economic, cultural, and spiritual anchor for communities in the Central Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Assin Manso Slave River Site safeguards the living history and ecological legacy of the Central Region for national scholars and global visitors alike."</blockquote>', 'Assin Manso, Cape Coast Highway', 'assin_manso.jpg', 0),
(37, 'Posuban Shrines', 'Central Region', 'Culture & Heritage', '<h3>Geographic & Architectural Overview</h3>
<p>Elaborate multi-story concrete shrines erected by Fante Asafo warrior companies, adorned with vibrant sculptures depicting ancestral proverbs and military strength.</p>
<p>Situated within the scenic environment of the <strong>Central Region</strong>, Posuban Shrines represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Posuban Shrines can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Central Region</li>
  <li><strong>Category:</strong> Culture & Heritage</li>
  <li><strong>Location / Access:</strong> Mankessim and Elmina streets</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Constructed in the late 19th and early 20th centuries as headquarters for traditional militia groups defending Fante coastal towns.</p>
<p>Spanning multiple centuries of Ghanaian history, Posuban Shrines has served as a key economic, cultural, and spiritual anchor for communities in the Central Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Posuban Shrines safeguards the living history and ecological legacy of the Central Region for national scholars and global visitors alike."</blockquote>', 'Mankessim and Elmina streets', 'posuban_shrines.jpg', 0),
(38, 'Brenu Beach', 'Central Region', 'Beach', '<h3>Geographic & Architectural Overview</h3>
<p>A pristine 3-kilometer stretch of white sand beach framed by coconut groves, offering calm waters ideal for eco-relaxation.</p>
<p>Situated within the scenic environment of the <strong>Central Region</strong>, Brenu Beach represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Brenu Beach can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Central Region</li>
  <li><strong>Category:</strong> Beach</li>
  <li><strong>Location / Access:</strong> Brenu Akyinim (15 km west of Elmina)</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Protected from pollution by local traditional decrees, preserving coastal bird habitats and sea turtle nesting zones.</p>
<p>Spanning multiple centuries of Ghanaian history, Brenu Beach has served as a key economic, cultural, and spiritual anchor for communities in the Central Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Brenu Beach safeguards the living history and ecological legacy of the Central Region for national scholars and global visitors alike."</blockquote>', 'Brenu Akyinim (15 km west of Elmina)', 'brenu_beach.jpg', 0),
(39, 'Winneba Lagoon and Muni-Pomadze Ramsar Site', 'Central Region', 'Nature & Wildlife', '<h3>Geographic & Architectural Overview</h3>
<p>A protected coastal lagoon and wetland complex providing crucial feeding grounds for thousands of international migratory waterbirds.</p>
<p>Situated within the scenic environment of the <strong>Central Region</strong>, Winneba Lagoon and Muni-Pomadze Ramsar Site represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Winneba Lagoon and Muni-Pomadze Ramsar Site can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Central Region</li>
  <li><strong>Category:</strong> Nature & Wildlife</li>
  <li><strong>Location / Access:</strong> Winneba Coastline</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Protected under the international Ramsar Convention, while serving as the sacred site for the annual Effutu Aboakyer deer-hunting festival.</p>
<p>Spanning multiple centuries of Ghanaian history, Winneba Lagoon and Muni-Pomadze Ramsar Site has served as a key economic, cultural, and spiritual anchor for communities in the Central Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Winneba Lagoon and Muni-Pomadze Ramsar Site safeguards the living history and ecological legacy of the Central Region for national scholars and global visitors alike."</blockquote>', 'Winneba Coastline', 'winneba_lagoon.jpg', 0),
(40, 'Fort Apollonia Museum', 'Central Region', 'History', '<h3>Geographic & Architectural Overview</h3>
<p>A restored stone beachfront fortress built by British merchants, now housing a regional museum of Nzema history and coastal commerce.</p>
<p>Situated within the scenic environment of the <strong>Central Region</strong>, Fort Apollonia Museum represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Fort Apollonia Museum can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Central Region</li>
  <li><strong>Category:</strong> History</li>
  <li><strong>Location / Access:</strong> Beyin Coast, Western/Central border</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Constructed between 1768 and 1770 following agreements with local Nzema chiefs to control gold and palm oil trading routes.</p>
<p>Spanning multiple centuries of Ghanaian history, Fort Apollonia Museum has served as a key economic, cultural, and spiritual anchor for communities in the Central Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Fort Apollonia Museum safeguards the living history and ecological legacy of the Central Region for national scholars and global visitors alike."</blockquote>', 'Beyin Coast, Western/Central border', 'fort_apollonia.jpg', 0),
(41, 'Fort Amsterdam', 'Central Region', 'History', '<h3>Geographic & Architectural Overview</h3>
<p>The majestic hilltop ruins of the very first military fort constructed by English merchants on the Gold Coast in 1638.</p>
<p>Situated within the scenic environment of the <strong>Central Region</strong>, Fort Amsterdam represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Fort Amsterdam can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Central Region</li>
  <li><strong>Category:</strong> History</li>
  <li><strong>Location / Access:</strong> Abandze Hilltop</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Captured by Dutch Admiral Engel de Ruyter in 1665 and renamed Fort Amsterdam, serving as a key military outpost along the central coast.</p>
<p>Spanning multiple centuries of Ghanaian history, Fort Amsterdam has served as a key economic, cultural, and spiritual anchor for communities in the Central Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Fort Amsterdam safeguards the living history and ecological legacy of the Central Region for national scholars and global visitors alike."</blockquote>', 'Abandze Hilltop', 'fort_amsterdam.jpg', 0),
(42, 'Fort San Sebastian', 'Central Region', 'History', '<h3>Geographic & Architectural Overview</h3>
<p>A well-preserved coastal fortress overlooking the Pra River estuary, featuring historic battlements and colonial archives.</p>
<p>Situated within the scenic environment of the <strong>Central Region</strong>, Fort San Sebastian represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Fort San Sebastian can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Central Region</li>
  <li><strong>Category:</strong> History</li>
  <li><strong>Location / Access:</strong> Shama Coastal Point</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Built by the Portuguese in 1526 to prevent rival European nations from trading along the Gold Coast, later expanded by the Dutch in 1640.</p>
<p>Spanning multiple centuries of Ghanaian history, Fort San Sebastian has served as a key economic, cultural, and spiritual anchor for communities in the Central Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Fort San Sebastian safeguards the living history and ecological legacy of the Central Region for national scholars and global visitors alike."</blockquote>', 'Shama Coastal Point', 'fort_san_sebastian.jpg', 0),
(43, 'Nzulezo Stilt Village', 'Western Region', 'Culture & Heritage', '<h3>Geographic & Architectural Overview</h3>
<p>A unique village built entirely on wooden stilts over the waters of Lake Amansuri, accessible only by a 45-minute wooden canoe ride through marshy waterways.</p>
<p>Situated within the scenic environment of the <strong>Western Region</strong>, Nzulezo Stilt Village represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Nzulezo Stilt Village can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Western Region</li>
  <li><strong>Category:</strong> Culture & Heritage</li>
  <li><strong>Location / Access:</strong> Lake Amansuri, near Beyin</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Founded over 500 years ago by migrants fleeing ethnic conflicts in Oualata (ancient Ghana Empire), guided by a sacred red-billed frog to build over water for defense.</p>
<p>Spanning multiple centuries of Ghanaian history, Nzulezo Stilt Village has served as a key economic, cultural, and spiritual anchor for communities in the Western Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Nzulezo Stilt Village safeguards the living history and ecological legacy of the Western Region for national scholars and global visitors alike."</blockquote>', 'Lake Amansuri, near Beyin', 'nzulezo.jpg', 1),
(44, 'Busua Beach', 'Western Region', 'Beach', '<h3>Geographic & Architectural Overview</h3>
<p>Ghana''s premier Atlantic surf beach, famous for consistent point breaks, golden sand, surf schools, and coastal eco-lodges.</p>
<p>Situated within the scenic environment of the <strong>Western Region</strong>, Busua Beach represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Busua Beach can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Western Region</li>
  <li><strong>Category:</strong> Beach</li>
  <li><strong>Location / Access:</strong> Busua Village, near Dixcove</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Busua developed from a quiet Ahanta fishing settlement into West Africa''s leading surfing sanctuary during the late 20th century.</p>
<p>Spanning multiple centuries of Ghanaian history, Busua Beach has served as a key economic, cultural, and spiritual anchor for communities in the Western Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Busua Beach safeguards the living history and ecological legacy of the Western Region for national scholars and global visitors alike."</blockquote>', 'Busua Village, near Dixcove', 'busua_beach.jpg', 1),
(45, 'Ankasa Conservation Area', 'Western Region', 'Nature & Wildlife', '<h3>Geographic & Architectural Overview</h3>
<p>A 500-square-kilometer wet evergreen rainforest boasting Ghana''s highest floral diversity index, home to rare forest elephants and chimpanzees.</p>
<p>Situated within the scenic environment of the <strong>Western Region</strong>, Ankasa Conservation Area represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Ankasa Conservation Area can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Western Region</li>
  <li><strong>Category:</strong> Nature & Wildlife</li>
  <li><strong>Location / Access:</strong> Elubo-Ivory Coast Frontier</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Protected as a strict nature reserve, preserving old-growth timber, twin bamboo cathedrals, and pristine river networks.</p>
<p>Spanning multiple centuries of Ghanaian history, Ankasa Conservation Area has served as a key economic, cultural, and spiritual anchor for communities in the Western Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Ankasa Conservation Area safeguards the living history and ecological legacy of the Western Region for national scholars and global visitors alike."</blockquote>', 'Elubo-Ivory Coast Frontier', 'ankasa.jpg', 0),
(46, 'Fort Metal Cross', 'Western Region', 'History', '<h3>Geographic & Architectural Overview</h3>
<p>A historic 1683 British fort overlooking a colorful bay filled with traditional hand-carved fishing canoes.</p>
<p>Situated within the scenic environment of the <strong>Western Region</strong>, Fort Metal Cross represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Fort Metal Cross can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Western Region</li>
  <li><strong>Category:</strong> History</li>
  <li><strong>Location / Access:</strong> Dixcove Fishing Bay</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Erected by the English Royal African Company to secure timber, gold, and slave trading rights in the Ahanta region.</p>
<p>Spanning multiple centuries of Ghanaian history, Fort Metal Cross has served as a key economic, cultural, and spiritual anchor for communities in the Western Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Fort Metal Cross safeguards the living history and ecological legacy of the Western Region for national scholars and global visitors alike."</blockquote>', 'Dixcove Fishing Bay', 'fort_metal_cross.jpg', 0),
(47, 'Axim Beach & Lou Moon Resort', 'Western Region', 'Beach', '<h3>Geographic & Architectural Overview</h3>
<p>A picturesque coastline featuring secluded sandy coves, rocky outcrops, offshore islands, and luxury eco-resorts.</p>
<p>Situated within the scenic environment of the <strong>Western Region</strong>, Axim Beach & Lou Moon Resort represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Axim Beach & Lou Moon Resort can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Western Region</li>
  <li><strong>Category:</strong> Beach</li>
  <li><strong>Location / Access:</strong> Axim Shoreline</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Axim served as an early gold export port for Portuguese, Dutch, and British traders from the 16th century onward.</p>
<p>Spanning multiple centuries of Ghanaian history, Axim Beach & Lou Moon Resort has served as a key economic, cultural, and spiritual anchor for communities in the Western Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Axim Beach & Lou Moon Resort safeguards the living history and ecological legacy of the Western Region for national scholars and global visitors alike."</blockquote>', 'Axim Shoreline', 'axim_beach.jpg', 1),
(48, 'Sekondi European Town', 'Western Region', 'History', '<h3>Geographic & Architectural Overview</h3>
<p>A neighborhood preserved with 19th-century colonial Dutch and British architectural compounds, old train stations, and administrative lodges.</p>
<p>Situated within the scenic environment of the <strong>Western Region</strong>, Sekondi European Town represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Sekondi European Town can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Western Region</li>
  <li><strong>Category:</strong> History</li>
  <li><strong>Location / Access:</strong> Twin City Metropolis, Sekondi</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Sekondi flourished as the primary Gold Coast railway terminus following the 1898 gold boom connecting mining areas to the coast.</p>
<p>Spanning multiple centuries of Ghanaian history, Sekondi European Town has served as a key economic, cultural, and spiritual anchor for communities in the Western Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Sekondi European Town safeguards the living history and ecological legacy of the Western Region for national scholars and global visitors alike."</blockquote>', 'Twin City Metropolis, Sekondi', 'sekondi_town.jpg', 0),
(49, 'Princess Town and Fort Fredericksburg', 'Western Region', 'History', '<h3>Geographic & Architectural Overview</h3>
<p>The historical remains of the only German (Brandenburger) military fortress constructed along the Gold Coast.</p>
<p>Situated within the scenic environment of the <strong>Western Region</strong>, Princess Town and Fort Fredericksburg represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Princess Town and Fort Fredericksburg can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Western Region</li>
  <li><strong>Category:</strong> History</li>
  <li><strong>Location / Access:</strong> Princess Town Village</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Constructed in 1683 by Prussian traders under Frederick William, Elector of Brandenburg, and famously defended by local chief John Conny.</p>
<p>Spanning multiple centuries of Ghanaian history, Princess Town and Fort Fredericksburg has served as a key economic, cultural, and spiritual anchor for communities in the Western Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Princess Town and Fort Fredericksburg safeguards the living history and ecological legacy of the Western Region for national scholars and global visitors alike."</blockquote>', 'Princess Town Village', 'fort_fredericksburg.jpg', 0),
(50, 'Cape Three Points', 'Western Region', 'Nature & Wildlife', '<h3>Geographic & Architectural Overview</h3>
<p>The southernmost tip of Ghana, featuring an active solar-powered lighthouse offering views of Atlantic ocean currents.</p>
<p>Situated within the scenic environment of the <strong>Western Region</strong>, Cape Three Points represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Cape Three Points can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Western Region</li>
  <li><strong>Category:</strong> Nature & Wildlife</li>
  <li><strong>Location / Access:</strong> Cape Three Points</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Dating back to an 1875 British lighthouse build, it marks the land point nearest to latitude 0°, longitude 0° (Null Island).</p>
<p>Spanning multiple centuries of Ghanaian history, Cape Three Points has served as a key economic, cultural, and spiritual anchor for communities in the Western Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Cape Three Points safeguards the living history and ecological legacy of the Western Region for national scholars and global visitors alike."</blockquote>', 'Cape Three Points', 'cape_three_points.jpg', 0),
(51, 'Butre Crocodile River', 'Western Region', 'Nature & Wildlife', '<h3>Geographic & Architectural Overview</h3>
<p>A tranquil coastal mangrove lagoon offering guided dugout canoe tours to observe native West African dwarf crocodiles and aquatic birds.</p>
<p>Situated within the scenic environment of the <strong>Western Region</strong>, Butre Crocodile River represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Butre Crocodile River can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Western Region</li>
  <li><strong>Category:</strong> Nature & Wildlife</li>
  <li><strong>Location / Access:</strong> Butre Village</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Managed by the Butre Eco-Tourism Board to generate sustainable local income while protecting wetland habitats.</p>
<p>Spanning multiple centuries of Ghanaian history, Butre Crocodile River has served as a key economic, cultural, and spiritual anchor for communities in the Western Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Butre Crocodile River safeguards the living history and ecological legacy of the Western Region for national scholars and global visitors alike."</blockquote>', 'Butre Village', 'butre_river.jpg', 0),
(52, 'Tamale Cultural Centre', 'Northern Region', 'Culture & Heritage', '<h3>Geographic & Architectural Overview</h3>
<p>Northern Ghana''s primary cultural complex showcasing workshops for traditional hand-woven smocks (fugu), leather craft, and Dagomba drumming.</p>
<p>Situated within the scenic environment of the <strong>Northern Region</strong>, Tamale Cultural Centre represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Tamale Cultural Centre can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Northern Region</li>
  <li><strong>Category:</strong> Culture & Heritage</li>
  <li><strong>Location / Access:</strong> Industrial Area, Tamale</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Established post-independence to preserve and market northern Ghanaian artistic traditions and indigenous craft skills.</p>
<p>Spanning multiple centuries of Ghanaian history, Tamale Cultural Centre has served as a key economic, cultural, and spiritual anchor for communities in the Northern Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Tamale Cultural Centre safeguards the living history and ecological legacy of the Northern Region for national scholars and global visitors alike."</blockquote>', 'Industrial Area, Tamale', 'tamale_cultural_center.jpg', 0),
(53, 'Tamale Central Mosque', 'Northern Region', 'Culture & Heritage', '<h3>Geographic & Architectural Overview</h3>
<p>A grand Islamic structure featuring tall minarets, acting as the spiritual center for Northern Ghana''s Muslim community.</p>
<p>Situated within the scenic environment of the <strong>Northern Region</strong>, Tamale Central Mosque represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Tamale Central Mosque can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Northern Region</li>
  <li><strong>Category:</strong> Culture & Heritage</li>
  <li><strong>Location / Access:</strong> Downtown Tamale</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Built in the 20th century, reflecting the centuries-old Islamic scholarly tradition introduced via trans-Saharan trade routes.</p>
<p>Spanning multiple centuries of Ghanaian history, Tamale Central Mosque has served as a key economic, cultural, and spiritual anchor for communities in the Northern Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Tamale Central Mosque safeguards the living history and ecological legacy of the Northern Region for national scholars and global visitors alike."</blockquote>', 'Downtown Tamale', 'tamale_mosque.jpg', 0),
(54, 'Daboya Smock Weaving Village', 'Northern Region', 'Culture & Heritage', '<h3>Geographic & Architectural Overview</h3>
<p>A famous riverside weaving village where artisans produce hand-spun cotton yarn and strip-woven smock fabrics using natural indigo dye pits.</p>
<p>Situated within the scenic environment of the <strong>Northern Region</strong>, Daboya Smock Weaving Village represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Daboya Smock Weaving Village can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Northern Region</li>
  <li><strong>Category:</strong> Culture & Heritage</li>
  <li><strong>Location / Access:</strong> Daboya, Northwest of Tamale</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Centuries-old textile capital of the Gonja Kingdom, renowned across West Africa for quality cotton craftsmanship.</p>
<p>Spanning multiple centuries of Ghanaian history, Daboya Smock Weaving Village has served as a key economic, cultural, and spiritual anchor for communities in the Northern Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Daboya Smock Weaving Village safeguards the living history and ecological legacy of the Northern Region for national scholars and global visitors alike."</blockquote>', 'Daboya, Northwest of Tamale', 'daboya_weaving.jpg', 0),
(55, 'Yendi Naa Gbewaa Palace', 'Northern Region', 'Culture & Heritage', '<h3>Geographic & Architectural Overview</h3>
<p>The official seat and residence of the Ya Naa, Overlord of the ancient Dagbon Kingdom.</p>
<p>Situated within the scenic environment of the <strong>Northern Region</strong>, Yendi Naa Gbewaa Palace represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Yendi Naa Gbewaa Palace can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Northern Region</li>
  <li><strong>Category:</strong> Culture & Heritage</li>
  <li><strong>Location / Access:</strong> Yendi Town</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Historical heart of the Dagbon Dynasty founded in the 15th century by descendants of the legendary warrior Naa Gbewaa.</p>
<p>Spanning multiple centuries of Ghanaian history, Yendi Naa Gbewaa Palace has served as a key economic, cultural, and spiritual anchor for communities in the Northern Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Yendi Naa Gbewaa Palace safeguards the living history and ecological legacy of the Northern Region for national scholars and global visitors alike."</blockquote>', 'Yendi Town', 'yendi_palace.jpg', 0),
(56, 'Wli Waterfalls', 'Volta Region', 'Nature & Wildlife', '<h3>Geographic & Architectural Overview</h3>
<p>West Africa''s tallest waterfall cascade, dropping 80 meters over a sheer cliff into a deep plunge pool surrounded by thousands of fruit bats.</p>
<p>Situated within the scenic environment of the <strong>Volta Region</strong>, Wli Waterfalls represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Wli Waterfalls can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Volta Region</li>
  <li><strong>Category:</strong> Nature & Wildlife</li>
  <li><strong>Location / Access:</strong> Agumatsa Wildlife Sanctuary, near Hohoe</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Preserved by the Agumatsa sanctuary guardians, Wli falls is revered as a sacred body of water giving life to surrounding communities.</p>
<p>Spanning multiple centuries of Ghanaian history, Wli Waterfalls has served as a key economic, cultural, and spiritual anchor for communities in the Volta Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Wli Waterfalls safeguards the living history and ecological legacy of the Volta Region for national scholars and global visitors alike."</blockquote>', 'Agumatsa Wildlife Sanctuary, near Hohoe', 'wli_falls.jpg', 1),
(57, 'Mount Afadjato', 'Volta Region', 'Nature & Wildlife', '<h3>Geographic & Architectural Overview</h3>
<p>Ghana''s highest peak at 885 meters above sea level, offering challenging rainforest hiking trails along the Togo border range.</p>
<p>Situated within the scenic environment of the <strong>Volta Region</strong>, Mount Afadjato represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Mount Afadjato can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Volta Region</li>
  <li><strong>Category:</strong> Nature & Wildlife</li>
  <li><strong>Location / Access:</strong> Liati Wote Village</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Named after the Ewe word ''Afadzato'' referring to wild ferns growing along the mountain slopes.</p>
<p>Spanning multiple centuries of Ghanaian history, Mount Afadjato has served as a key economic, cultural, and spiritual anchor for communities in the Volta Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Mount Afadjato safeguards the living history and ecological legacy of the Volta Region for national scholars and global visitors alike."</blockquote>', 'Liati Wote Village', 'mount_afadjato.jpg', 1),
(58, 'Tafi Atome Monkey Sanctuary', 'Volta Region', 'Nature & Wildlife', '<h3>Geographic & Architectural Overview</h3>
<p>A sacred mahogany forest where Mona monkeys are protected by traditional taboos and live harmoniously alongside villagers.</p>
<p>Situated within the scenic environment of the <strong>Volta Region</strong>, Tafi Atome Monkey Sanctuary represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Tafi Atome Monkey Sanctuary can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Volta Region</li>
  <li><strong>Category:</strong> Nature & Wildlife</li>
  <li><strong>Location / Access:</strong> Tafi Atome Village, near Ho</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Local spiritual decree has protected these primates for over 200 years as sacred messengers of the gods.</p>
<p>Spanning multiple centuries of Ghanaian history, Tafi Atome Monkey Sanctuary has served as a key economic, cultural, and spiritual anchor for communities in the Volta Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Tafi Atome Monkey Sanctuary safeguards the living history and ecological legacy of the Volta Region for national scholars and global visitors alike."</blockquote>', 'Tafi Atome Village, near Ho', 'tafi_atome.jpg', 0),
(59, 'Amedzofe Village', 'Volta Region', 'Nature & Wildlife', '<h3>Geographic & Architectural Overview</h3>
<p>Ghana''s highest human settlement at 750 meters elevation, featuring cool mountain weather, Mount Gemi cross, and canopy walkways.</p>
<p>Situated within the scenic environment of the <strong>Volta Region</strong>, Amedzofe Village represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Amedzofe Village can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Volta Region</li>
  <li><strong>Category:</strong> Nature & Wildlife</li>
  <li><strong>Location / Access:</strong> Avatime Mountains</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Founded in the Avatime hills, Amedzofe served as a German missionary station during the late 19th century.</p>
<p>Spanning multiple centuries of Ghanaian history, Amedzofe Village has served as a key economic, cultural, and spiritual anchor for communities in the Volta Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Amedzofe Village safeguards the living history and ecological legacy of the Volta Region for national scholars and global visitors alike."</blockquote>', 'Avatime Mountains', 'amedzofe.jpg', 0),
(60, 'Keta Lagoon and Fort Prinzenstein', 'Volta Region', 'History', '<h3>Geographic & Architectural Overview</h3>
<p>A massive marine saltwater lagoon bordered by the dramatic ruins of a 1784 Danish fortress.</p>
<p>Situated within the scenic environment of the <strong>Volta Region</strong>, Keta Lagoon and Fort Prinzenstein represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Keta Lagoon and Fort Prinzenstein can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Volta Region</li>
  <li><strong>Category:</strong> History</li>
  <li><strong>Location / Access:</strong> Keta Sandbar</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Built by the Danes following the Anlo-Danish war to secure trade routes and fortify coastal claims.</p>
<p>Spanning multiple centuries of Ghanaian history, Keta Lagoon and Fort Prinzenstein has served as a key economic, cultural, and spiritual anchor for communities in the Volta Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Keta Lagoon and Fort Prinzenstein safeguards the living history and ecological legacy of the Volta Region for national scholars and global visitors alike."</blockquote>', 'Keta Sandbar', 'keta_lagoon.jpg', 0),
(61, 'Tagbo Falls', 'Volta Region', 'Nature & Wildlife', '<h3>Geographic & Architectural Overview</h3>
<p>A rainforest waterfall cascading down a lush amphitheater of green rock inside the Afadjato mountain basin.</p>
<p>Situated within the scenic environment of the <strong>Volta Region</strong>, Tagbo Falls represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Tagbo Falls can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Volta Region</li>
  <li><strong>Category:</strong> Nature & Wildlife</li>
  <li><strong>Location / Access:</strong> Liati Wote</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Discovered by local hunters, Tagbo Falls provides water to downstream cocoa farming communities.</p>
<p>Spanning multiple centuries of Ghanaian history, Tagbo Falls has served as a key economic, cultural, and spiritual anchor for communities in the Volta Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Tagbo Falls safeguards the living history and ecological legacy of the Volta Region for national scholars and global visitors alike."</blockquote>', 'Liati Wote', 'tagbo_falls.jpg', 0),
(62, 'Logba Tota Caves', 'Volta Region', 'Nature & Wildlife', '<h3>Geographic & Architectural Overview</h3>
<p>Subterranean limestone caverns containing ancient stalactites, stalagmites, and fruit bat colonies.</p>
<p>Situated within the scenic environment of the <strong>Volta Region</strong>, Logba Tota Caves represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Logba Tota Caves can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Volta Region</li>
  <li><strong>Category:</strong> Nature & Wildlife</li>
  <li><strong>Location / Access:</strong> Logba Tota Village</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Utilized as natural defensive hideouts by Logba ancestors during 18th-century tribal conflicts.</p>
<p>Spanning multiple centuries of Ghanaian history, Logba Tota Caves has served as a key economic, cultural, and spiritual anchor for communities in the Volta Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Logba Tota Caves safeguards the living history and ecological legacy of the Volta Region for national scholars and global visitors alike."</blockquote>', 'Logba Tota Village', 'logba_caves.jpg', 0),
(63, 'Mount Agou Viewpoint', 'Volta Region', 'Nature & Wildlife', '<h3>Geographic & Architectural Overview</h3>
<p>An elevated mountain lookout tracking along the Ghana-Togo mountain boundary.</p>
<p>Situated within the scenic environment of the <strong>Volta Region</strong>, Mount Agou Viewpoint represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Mount Agou Viewpoint can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Volta Region</li>
  <li><strong>Category:</strong> Nature & Wildlife</li>
  <li><strong>Location / Access:</strong> Near Ho mountain pass</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Offers panoramic perspectives of the Volta lake basin and dense agricultural forest valleys.</p>
<p>Spanning multiple centuries of Ghanaian history, Mount Agou Viewpoint has served as a key economic, cultural, and spiritual anchor for communities in the Volta Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Mount Agou Viewpoint safeguards the living history and ecological legacy of the Volta Region for national scholars and global visitors alike."</blockquote>', 'Near Ho mountain pass', 'mount_agou.jpg', 0),
(64, 'Aburi Botanical Gardens', 'Eastern Region', 'Nature & Wildlife', '<h3>Geographic & Architectural Overview</h3>
<p>A 19th-century botanical garden featuring royal palm avenues, exotic timber species, and cool hill-country weather.</p>
<p>Situated within the scenic environment of the <strong>Eastern Region</strong>, Aburi Botanical Gardens represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Aburi Botanical Gardens can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Eastern Region</li>
  <li><strong>Category:</strong> Nature & Wildlife</li>
  <li><strong>Location / Access:</strong> Aburi, Akwapim Hills</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Opened in 1890 under British Governor Sir William Brandford Griffith as a tropical sanatorium and agricultural research station.</p>
<p>Spanning multiple centuries of Ghanaian history, Aburi Botanical Gardens has served as a key economic, cultural, and spiritual anchor for communities in the Eastern Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Aburi Botanical Gardens safeguards the living history and ecological legacy of the Eastern Region for national scholars and global visitors alike."</blockquote>', 'Aburi, Akwapim Hills', 'aburi_gardens.jpg', 1),
(65, 'Boti Waterfalls', 'Eastern Region', 'Nature & Wildlife', '<h3>Geographic & Architectural Overview</h3>
<p>Twin waterfalls plunging over a rocky precipice into a rainforest basin, culturally designated as male and female falls.</p>
<p>Situated within the scenic environment of the <strong>Eastern Region</strong>, Boti Waterfalls represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Boti Waterfalls can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Eastern Region</li>
  <li><strong>Category:</strong> Nature & Wildlife</li>
  <li><strong>Location / Access:</strong> Huhunya, Yilo Krobo District</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Revered by the Yilo Krobo people as a divine gift of nature, swelling into a single roaring fall during high rain periods.</p>
<p>Spanning multiple centuries of Ghanaian history, Boti Waterfalls has served as a key economic, cultural, and spiritual anchor for communities in the Eastern Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Boti Waterfalls safeguards the living history and ecological legacy of the Eastern Region for national scholars and global visitors alike."</blockquote>', 'Huhunya, Yilo Krobo District', 'boti_falls.jpg', 1),
(66, 'Umbrella Rock', 'Eastern Region', 'Nature & Wildlife', '<h3>Geographic & Architectural Overview</h3>
<p>A unique mushroom-shaped geological overhang rock formation capable of sheltering up to 15 people.</p>
<p>Situated within the scenic environment of the <strong>Eastern Region</strong>, Umbrella Rock represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Umbrella Rock can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Eastern Region</li>
  <li><strong>Category:</strong> Nature & Wildlife</li>
  <li><strong>Location / Access:</strong> Yilo Krobo Forest (Hike from Boti)</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Formed over millions of years through natural wind and water erosion inside the Boti rainforest basin.</p>
<p>Spanning multiple centuries of Ghanaian history, Umbrella Rock has served as a key economic, cultural, and spiritual anchor for communities in the Eastern Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Umbrella Rock safeguards the living history and ecological legacy of the Eastern Region for national scholars and global visitors alike."</blockquote>', 'Yilo Krobo Forest (Hike from Boti)', 'umbrella_rock.jpg', 0),
(67, 'Three-Headed Palm Tree', 'Eastern Region', 'Nature & Wildlife', '<h3>Geographic & Architectural Overview</h3>
<p>A rare botanical anomaly featuring a single palm tree base branching into three fruit-bearing tops.</p>
<p>Situated within the scenic environment of the <strong>Eastern Region</strong>, Three-Headed Palm Tree represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Three-Headed Palm Tree can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Eastern Region</li>
  <li><strong>Category:</strong> Nature & Wildlife</li>
  <li><strong>Location / Access:</strong> Boti Forest Basin</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Revered by local herbalists and traditionalists as a symbol of natural mystery and spiritual energy.</p>
<p>Spanning multiple centuries of Ghanaian history, Three-Headed Palm Tree has served as a key economic, cultural, and spiritual anchor for communities in the Eastern Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Three-Headed Palm Tree safeguards the living history and ecological legacy of the Eastern Region for national scholars and global visitors alike."</blockquote>', 'Boti Forest Basin', 'three_headed_palm.jpg', 0),
(68, 'Asenema Waterfalls', 'Eastern Region', 'Nature & Wildlife', '<h3>Geographic & Architectural Overview</h3>
<p>A tranquil, shaded waterfall cascading down a steep rocky gorge surrounded by lush fern vegetation.</p>
<p>Situated within the scenic environment of the <strong>Eastern Region</strong>, Asenema Waterfalls represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Asenema Waterfalls can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Eastern Region</li>
  <li><strong>Category:</strong> Nature & Wildlife</li>
  <li><strong>Location / Access:</strong> Asenema Village, Okere District</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Fed by pristine mountain streams flowing through the Akwapim ridge.</p>
<p>Spanning multiple centuries of Ghanaian history, Asenema Waterfalls has served as a key economic, cultural, and spiritual anchor for communities in the Eastern Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Asenema Waterfalls safeguards the living history and ecological legacy of the Eastern Region for national scholars and global visitors alike."</blockquote>', 'Asenema Village, Okere District', 'asenema_falls.jpg', 0),
(69, 'Adom Waterfalls', 'Eastern Region', 'Nature & Wildlife', '<h3>Geographic & Architectural Overview</h3>
<p>A multi-tiered waterfall cascading over natural stone steps inside a quiet forest sanctuary.</p>
<p>Situated within the scenic environment of the <strong>Eastern Region</strong>, Adom Waterfalls represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Adom Waterfalls can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Eastern Region</li>
  <li><strong>Category:</strong> Nature & Wildlife</li>
  <li><strong>Location / Access:</strong> Obosomase, Akwapim Hills</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>A popular retreat spot for eco-tourists and nature hikers in the Akwapim mountains.</p>
<p>Spanning multiple centuries of Ghanaian history, Adom Waterfalls has served as a key economic, cultural, and spiritual anchor for communities in the Eastern Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Adom Waterfalls safeguards the living history and ecological legacy of the Eastern Region for national scholars and global visitors alike."</blockquote>', 'Obosomase, Akwapim Hills', 'adom_falls.jpg', 0),
(70, 'Bunso Eco Park', 'Eastern Region', 'Nature & Wildlife', '<h3>Geographic & Architectural Overview</h3>
<p>An arboretum and eco-park featuring canopy walkways, ziplines, and over 110 species of medicinal trees.</p>
<p>Situated within the scenic environment of the <strong>Eastern Region</strong>, Bunso Eco Park represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Bunso Eco Park can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Eastern Region</li>
  <li><strong>Category:</strong> Nature & Wildlife</li>
  <li><strong>Location / Access:</strong> Bunso, off Accra-Kumasi Highway</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Established as a timber and cocoa research site, now offering interactive eco-adventure activities.</p>
<p>Spanning multiple centuries of Ghanaian history, Bunso Eco Park has served as a key economic, cultural, and spiritual anchor for communities in the Eastern Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Bunso Eco Park safeguards the living history and ecological legacy of the Eastern Region for national scholars and global visitors alike."</blockquote>', 'Bunso, off Accra-Kumasi Highway', 'bunso_eco_park.jpg', 0),
(71, 'Akosombo Dam & Lake Volta', 'Eastern Region', 'Nature & Waterways', '<h3>Geographic & Architectural Overview</h3>
<p>A colossal hydroelectric dam blocking the Volta River gorge, creating Lake Volta—one of the largest man-made lakes on Earth.</p>
<p>Situated within the scenic environment of the <strong>Eastern Region</strong>, Akosombo Dam & Lake Volta represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Akosombo Dam & Lake Volta can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Eastern Region</li>
  <li><strong>Category:</strong> Nature & Waterways</li>
  <li><strong>Location / Access:</strong> Akosombo, Eastern Region</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Constructed between 1961 and 1965 under President Kwame Nkrumah to power Ghana''s industrial grid.</p>
<p>Spanning multiple centuries of Ghanaian history, Akosombo Dam & Lake Volta has served as a key economic, cultural, and spiritual anchor for communities in the Eastern Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Akosombo Dam & Lake Volta safeguards the living history and ecological legacy of the Eastern Region for national scholars and global visitors alike."</blockquote>', 'Akosombo, Eastern Region', 'lake_volta.jpg', 1),
(72, 'Safari Valley Resort', 'Eastern Region', 'Nature & Wildlife', '<h3>Geographic & Architectural Overview</h3>
<p>A luxury eco-resort featuring manicured gardens, exotic animal ranches, sports courts, and eco-lodges.</p>
<p>Situated within the scenic environment of the <strong>Eastern Region</strong>, Safari Valley Resort represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Safari Valley Resort can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Eastern Region</li>
  <li><strong>Category:</strong> Nature & Wildlife</li>
  <li><strong>Location / Access:</strong> Adukrom, Akwapim District</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Developed as a premier modern eco-destination promoting sustainable leisure tourism in Ghana.</p>
<p>Spanning multiple centuries of Ghanaian history, Safari Valley Resort has served as a key economic, cultural, and spiritual anchor for communities in the Eastern Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Safari Valley Resort safeguards the living history and ecological legacy of the Eastern Region for national scholars and global visitors alike."</blockquote>', 'Adukrom, Akwapim District', 'safari_valley.jpg', 1),
(73, 'Dodi Island', 'Eastern Region', 'Nature & Waterways', '<h3>Geographic & Architectural Overview</h3>
<p>An island retreat inside Lake Volta accessible via the historic Dodi Princess cruise boat.</p>
<p>Situated within the scenic environment of the <strong>Eastern Region</strong>, Dodi Island represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Dodi Island can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Eastern Region</li>
  <li><strong>Category:</strong> Nature & Waterways</li>
  <li><strong>Location / Access:</strong> Lake Volta (Cruise from Akosombo)</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Created when the Akosombo Dam flooded the Volta basin in the mid-1960s.</p>
<p>Spanning multiple centuries of Ghanaian history, Dodi Island has served as a key economic, cultural, and spiritual anchor for communities in the Eastern Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Dodi Island safeguards the living history and ecological legacy of the Eastern Region for national scholars and global visitors alike."</blockquote>', 'Lake Volta (Cruise from Akosombo)', 'dodi_island.jpg', 0),
(74, 'Koforidua Bead Market', 'Eastern Region', 'Culture & Heritage', '<h3>Geographic & Architectural Overview</h3>
<p>West Africa''s largest weekly glass bead trade market, operating every Thursday.</p>
<p>Situated within the scenic environment of the <strong>Eastern Region</strong>, Koforidua Bead Market represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Koforidua Bead Market can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Eastern Region</li>
  <li><strong>Category:</strong> Culture & Heritage</li>
  <li><strong>Location / Access:</strong> Koforidua Town Center</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Centuries-old trading market where Krobo and Ashanti beadmakers sell hand-crafted glass beads.</p>
<p>Spanning multiple centuries of Ghanaian history, Koforidua Bead Market has served as a key economic, cultural, and spiritual anchor for communities in the Eastern Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Koforidua Bead Market safeguards the living history and ecological legacy of the Eastern Region for national scholars and global visitors alike."</blockquote>', 'Koforidua Town Center', 'koforidua_bead_market.jpg', 0),
(75, 'Sajuna Beach Club', 'Eastern Region', 'Nature & Waterways', '<h3>Geographic & Architectural Overview</h3>
<p>A riverfront resort along the Volta River offering water sports, kayaking, and beach games.</p>
<p>Situated within the scenic environment of the <strong>Eastern Region</strong>, Sajuna Beach Club represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Sajuna Beach Club can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Eastern Region</li>
  <li><strong>Category:</strong> Nature & Waterways</li>
  <li><strong>Location / Access:</strong> Atimpoku, near Adomi Bridge</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Pioneered modern riverfront leisure developments near the iconic Adomi Bridge.</p>
<p>Spanning multiple centuries of Ghanaian history, Sajuna Beach Club has served as a key economic, cultural, and spiritual anchor for communities in the Eastern Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Sajuna Beach Club safeguards the living history and ecological legacy of the Eastern Region for national scholars and global visitors alike."</blockquote>', 'Atimpoku, near Adomi Bridge', 'sajuna_beach.jpg', 0),
(76, 'Paga Crocodile Pond', 'Upper East Region', 'Nature & Wildlife', '<h3>Geographic & Architectural Overview</h3>
<p>A sacred pool where wild, protected Nile crocodiles live peacefully with villagers and allow visitors to touch them.</p>
<p>Situated within the scenic environment of the <strong>Upper East Region</strong>, Paga Crocodile Pond represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Paga Crocodile Pond can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Upper East Region</li>
  <li><strong>Category:</strong> Nature & Wildlife</li>
  <li><strong>Location / Access:</strong> Paga, Burkina Faso Border</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Protected due to an ancient legend where a crocodile saved the village founder Nave from dying of thirst.</p>
<p>Spanning multiple centuries of Ghanaian history, Paga Crocodile Pond has served as a key economic, cultural, and spiritual anchor for communities in the Upper East Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Paga Crocodile Pond safeguards the living history and ecological legacy of the Upper East Region for national scholars and global visitors alike."</blockquote>', 'Paga, Burkina Faso Border', 'paga_crocodiles.jpg', 1),
(77, 'Tongo Hills and Tengzug Shrine', 'Upper East Region', 'Culture & Heritage', '<h3>Geographic & Architectural Overview</h3>
<p>A dramatic granitic boulder landscape hiding the ancient subterranean oracle shrine of the Talensi people.</p>
<p>Situated within the scenic environment of the <strong>Upper East Region</strong>, Tongo Hills and Tengzug Shrine represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Tongo Hills and Tengzug Shrine can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Upper East Region</li>
  <li><strong>Category:</strong> Culture & Heritage</li>
  <li><strong>Location / Access:</strong> Tongo, South of Bolgatanga</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Served as an unassailable mountain fortress against colonial forces during 19th-century resistance wars.</p>
<p>Spanning multiple centuries of Ghanaian history, Tongo Hills and Tengzug Shrine has served as a key economic, cultural, and spiritual anchor for communities in the Upper East Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Tongo Hills and Tengzug Shrine safeguards the living history and ecological legacy of the Upper East Region for national scholars and global visitors alike."</blockquote>', 'Tongo, South of Bolgatanga', 'tongo_hills.jpg', 0),
(78, 'Sirigu Pottery and Art Village', 'Upper East Region', 'Culture & Heritage', '<h3>Geographic & Architectural Overview</h3>
<p>An indigenous village where women paint intricate geometric traditional patterns on earthen house walls.</p>
<p>Situated within the scenic environment of the <strong>Upper East Region</strong>, Sirigu Pottery and Art Village represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Sirigu Pottery and Art Village can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Upper East Region</li>
  <li><strong>Category:</strong> Culture & Heritage</li>
  <li><strong>Location / Access:</strong> Sirigu, Upper East</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Preserved by SWOPA to sustain ancient Kassena mural painting techniques across generations.</p>
<p>Spanning multiple centuries of Ghanaian history, Sirigu Pottery and Art Village has served as a key economic, cultural, and spiritual anchor for communities in the Upper East Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Sirigu Pottery and Art Village safeguards the living history and ecological legacy of the Upper East Region for national scholars and global visitors alike."</blockquote>', 'Sirigu, Upper East', 'sirigu_pottery.jpg', 0),
(79, 'Pikworo Slave Camp', 'Upper East Region', 'History', '<h3>Geographic & Architectural Overview</h3>
<p>An open-air slave transit camp featuring stone eating bowls carved into granite rocks where captives fed.</p>
<p>Situated within the scenic environment of the <strong>Upper East Region</strong>, Pikworo Slave Camp represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Pikworo Slave Camp can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Upper East Region</li>
  <li><strong>Category:</strong> History</li>
  <li><strong>Location / Access:</strong> Paga Nania</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Established in 1704 as a staging point for slaves auctioned by raiders Samori and Babatu.</p>
<p>Spanning multiple centuries of Ghanaian history, Pikworo Slave Camp has served as a key economic, cultural, and spiritual anchor for communities in the Upper East Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Pikworo Slave Camp safeguards the living history and ecological legacy of the Upper East Region for national scholars and global visitors alike."</blockquote>', 'Paga Nania', 'pikworo_slave_camp.jpg', 0),
(80, 'Navrongo Cathedral', 'Upper East Region', 'History', '<h3>Geographic & Architectural Overview</h3>
<p>A unique 1906 basilica constructed entirely from mud bricks by French White Fathers missionaries.</p>
<p>Situated within the scenic environment of the <strong>Upper East Region</strong>, Navrongo Cathedral represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Navrongo Cathedral can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Upper East Region</li>
  <li><strong>Category:</strong> History</li>
  <li><strong>Location / Access:</strong> Navrongo Town</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Built using local earthen building methods blended with European ecclesiastical architecture.</p>
<p>Spanning multiple centuries of Ghanaian history, Navrongo Cathedral has served as a key economic, cultural, and spiritual anchor for communities in the Upper East Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Navrongo Cathedral safeguards the living history and ecological legacy of the Upper East Region for national scholars and global visitors alike."</blockquote>', 'Navrongo Town', 'navrongo_cathedral.jpg', 0),
(81, 'Wa Naa''s Palace', 'Upper West Region', 'Culture & Heritage', '<h3>Geographic & Architectural Overview</h3>
<p>The royal palace of the Wa Naa (Wala King), showcasing traditional Sudanese-style earthen architecture.</p>
<p>Situated within the scenic environment of the <strong>Upper West Region</strong>, Wa Naa''s Palace represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Wa Naa''s Palace can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Upper West Region</li>
  <li><strong>Category:</strong> Culture & Heritage</li>
  <li><strong>Location / Access:</strong> Wa Town Centre</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>19th-century earthen palace featuring buttressed walls and royal burial grounds.</p>
<p>Spanning multiple centuries of Ghanaian history, Wa Naa''s Palace has served as a key economic, cultural, and spiritual anchor for communities in the Upper West Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Wa Naa''s Palace safeguards the living history and ecological legacy of the Upper West Region for national scholars and global visitors alike."</blockquote>', 'Wa Town Centre', 'wa_naa_palace.jpg', 0),
(82, 'Gwollu Slave Defense Wall', 'Upper West Region', 'History', '<h3>Geographic & Architectural Overview</h3>
<p>Earthen defense wall fortifications built in the 19th century to protect Gwollu residents from slave raiders.</p>
<p>Situated within the scenic environment of the <strong>Upper West Region</strong>, Gwollu Slave Defense Wall represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Gwollu Slave Defense Wall can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Upper West Region</li>
  <li><strong>Category:</strong> History</li>
  <li><strong>Location / Access:</strong> Gwollu, near Tumu</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Constructed under Gwollu Koro Tanjia to withstand sieges by notorious slave merchants Babatu and Gazare.</p>
<p>Spanning multiple centuries of Ghanaian history, Gwollu Slave Defense Wall has served as a key economic, cultural, and spiritual anchor for communities in the Upper West Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Gwollu Slave Defense Wall safeguards the living history and ecological legacy of the Upper West Region for national scholars and global visitors alike."</blockquote>', 'Gwollu, near Tumu', 'gwollu_wall.jpg', 0),
(83, 'Wechiau Hippo Sanctuary', 'Upper West Region', 'Nature & Wildlife', '<h3>Geographic & Architectural Overview</h3>
<p>A community-managed reserve along the Black Volta River protecting wild hippopotamus populations.</p>
<p>Situated within the scenic environment of the <strong>Upper West Region</strong>, Wechiau Hippo Sanctuary represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Wechiau Hippo Sanctuary can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Upper West Region</li>
  <li><strong>Category:</strong> Nature & Wildlife</li>
  <li><strong>Location / Access:</strong> Wechiau, Black Volta River</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Established in 1999 by local chiefs to conserve hippos and boost sustainable eco-tourism.</p>
<p>Spanning multiple centuries of Ghanaian history, Wechiau Hippo Sanctuary has served as a key economic, cultural, and spiritual anchor for communities in the Upper West Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Wechiau Hippo Sanctuary safeguards the living history and ecological legacy of the Upper West Region for national scholars and global visitors alike."</blockquote>', 'Wechiau, Black Volta River', 'wechiau_hippo.jpg', 0),
(84, 'Gbelle Game Reserve', 'Upper West Region', 'Nature & Wildlife', '<h3>Geographic & Architectural Overview</h3>
<p>A dry savanna game reserve protecting seasonal migrations of savanna elephants, antelopes, and waterbucks.</p>
<p>Situated within the scenic environment of the <strong>Upper West Region</strong>, Gbelle Game Reserve represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Gbelle Game Reserve can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Upper West Region</li>
  <li><strong>Category:</strong> Nature & Wildlife</li>
  <li><strong>Location / Access:</strong> Tumu District</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Gazetted to preserve dry northern woodland ecosystems and endangered bird species.</p>
<p>Spanning multiple centuries of Ghanaian history, Gbelle Game Reserve has served as a key economic, cultural, and spiritual anchor for communities in the Upper West Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Gbelle Game Reserve safeguards the living history and ecological legacy of the Upper West Region for national scholars and global visitors alike."</blockquote>', 'Tumu District', 'gbelle_reserve.jpg', 0),
(85, 'Boabeng-Fiema Monkey Sanctuary', 'Bono East Region', 'Nature & Wildlife', '<h3>Geographic & Architectural Overview</h3>
<p>A sacred forest sanctuary where Mona and Colobus monkeys live closely with humans and receive human-style funerals upon death.</p>
<p>Situated within the scenic environment of the <strong>Bono East Region</strong>, Boabeng-Fiema Monkey Sanctuary represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Boabeng-Fiema Monkey Sanctuary can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Bono East Region</li>
  <li><strong>Category:</strong> Nature & Wildlife</li>
  <li><strong>Location / Access:</strong> Nkoranza District</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Monkeys have been protected by traditional spiritual laws since 1840 after a local fetish priest declared them sacred.</p>
<p>Spanning multiple centuries of Ghanaian history, Boabeng-Fiema Monkey Sanctuary has served as a key economic, cultural, and spiritual anchor for communities in the Bono East Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Boabeng-Fiema Monkey Sanctuary safeguards the living history and ecological legacy of the Bono East Region for national scholars and global visitors alike."</blockquote>', 'Nkoranza District', 'boabeng_fiema.jpg', 1),
(86, 'Kintampo Waterfalls', 'Bono East Region', 'Nature & Wildlife', '<h3>Geographic & Architectural Overview</h3>
<p>A 3-stage waterfall cascading 70 meters down a forested canyon, marking the geographical center of Ghana.</p>
<p>Situated within the scenic environment of the <strong>Bono East Region</strong>, Kintampo Waterfalls represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Kintampo Waterfalls can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Bono East Region</li>
  <li><strong>Category:</strong> Nature & Wildlife</li>
  <li><strong>Location / Access:</strong> Kintampo Highway</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Fed by the Pumpum River, Kintampo Falls has served as a landmark resting stop since pre-colonial times.</p>
<p>Spanning multiple centuries of Ghanaian history, Kintampo Waterfalls has served as a key economic, cultural, and spiritual anchor for communities in the Bono East Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Kintampo Waterfalls safeguards the living history and ecological legacy of the Bono East Region for national scholars and global visitors alike."</blockquote>', 'Kintampo Highway', 'kintampo_falls.jpg', 1),
(87, 'Fuller Falls', 'Bono East Region', 'Nature & Wildlife', '<h3>Geographic & Architectural Overview</h3>
<p>A peaceful, multi-tiered waterfall cascading over gentle rocky ledges into a shaded pool.</p>
<p>Situated within the scenic environment of the <strong>Bono East Region</strong>, Fuller Falls represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Fuller Falls can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Bono East Region</li>
  <li><strong>Category:</strong> Nature & Wildlife</li>
  <li><strong>Location / Access:</strong> Kintampo West</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Discovered by Italian missionary Father Joseph Fuller in 1988 and developed for quiet eco-relaxation.</p>
<p>Spanning multiple centuries of Ghanaian history, Fuller Falls has served as a key economic, cultural, and spiritual anchor for communities in the Bono East Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Fuller Falls safeguards the living history and ecological legacy of the Bono East Region for national scholars and global visitors alike."</blockquote>', 'Kintampo West', 'fuller_falls.jpg', 0),
(88, 'Buoyem Caves and Sacred Grove', 'Bono East Region', 'Nature & Wildlife', '<h3>Geographic & Architectural Overview</h3>
<p>Sandstone caves and sacred forests harboring giant fruit bat colonies and geological pillars.</p>
<p>Situated within the scenic environment of the <strong>Bono East Region</strong>, Buoyem Caves and Sacred Grove represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Buoyem Caves and Sacred Grove can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Bono East Region</li>
  <li><strong>Category:</strong> Nature & Wildlife</li>
  <li><strong>Location / Access:</strong> Techiman Municipality</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Revered by Techiman ancestors as a spiritual sanctuary and military refuge.</p>
<p>Spanning multiple centuries of Ghanaian history, Buoyem Caves and Sacred Grove has served as a key economic, cultural, and spiritual anchor for communities in the Bono East Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Buoyem Caves and Sacred Grove safeguards the living history and ecological legacy of the Bono East Region for national scholars and global visitors alike."</blockquote>', 'Techiman Municipality', 'buoyem_caves.jpg', 0),
(89, 'Tanoboase Sacred Grove', 'Bono Region', 'Culture & Heritage', '<h3>Geographic & Architectural Overview</h3>
<p>Massive rock outcrops and sacred groves that served as defensive hideouts for the Bono people.</p>
<p>Situated within the scenic environment of the <strong>Bono Region</strong>, Tanoboase Sacred Grove represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Tanoboase Sacred Grove can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Bono Region</li>
  <li><strong>Category:</strong> Culture & Heritage</li>
  <li><strong>Location / Access:</strong> Techiman-Tuobodom Road</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Believed to be the ancestral birthplace of the Bono people, protecting ancient traditional shrines.</p>
<p>Spanning multiple centuries of Ghanaian history, Tanoboase Sacred Grove has served as a key economic, cultural, and spiritual anchor for communities in the Bono Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Tanoboase Sacred Grove safeguards the living history and ecological legacy of the Bono Region for national scholars and global visitors alike."</blockquote>', 'Techiman-Tuobodom Road', 'tanoboase_grove.jpg', 0),
(90, 'Duasidan Monkey Sanctuary', 'Bono Region', 'Nature & Wildlife', '<h3>Geographic & Architectural Overview</h3>
<p>A community forest patch sheltering wild Mona monkey troops in the Dormaa area.</p>
<p>Situated within the scenic environment of the <strong>Bono Region</strong>, Duasidan Monkey Sanctuary represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Duasidan Monkey Sanctuary can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Bono Region</li>
  <li><strong>Category:</strong> Nature & Wildlife</li>
  <li><strong>Location / Access:</strong> Dormaa Ahenkro District</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Preserved through traditional taboo enforcement prohibiting the hunting of primates.</p>
<p>Spanning multiple centuries of Ghanaian history, Duasidan Monkey Sanctuary has served as a key economic, cultural, and spiritual anchor for communities in the Bono Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Duasidan Monkey Sanctuary safeguards the living history and ecological legacy of the Bono Region for national scholars and global visitors alike."</blockquote>', 'Dormaa Ahenkro District', 'duasidan_monkeys.jpg', 0),
(91, 'Mole National Park', 'Savannah Region', 'Nature & Wildlife', '<h3>Geographic & Architectural Overview</h3>
<p>Ghana''s largest savanna reserve covering 4,576 sq km, famous for walking safaris with wild elephants, baboons, and antelopes.</p>
<p>Situated within the scenic environment of the <strong>Savannah Region</strong>, Mole National Park represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Mole National Park can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Savannah Region</li>
  <li><strong>Category:</strong> Nature & Wildlife</li>
  <li><strong>Location / Access:</strong> Damongo, near Larabanga</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Gazetted in 1971, Mole protects West Africa''s savanna biodiversity and historic trade trails.</p>
<p>Spanning multiple centuries of Ghanaian history, Mole National Park has served as a key economic, cultural, and spiritual anchor for communities in the Savannah Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Mole National Park safeguards the living history and ecological legacy of the Savannah Region for national scholars and global visitors alike."</blockquote>', 'Damongo, near Larabanga', 'mole_park.jpg', 1),
(92, 'Larabanga Mosque', 'Savannah Region', 'History', '<h3>Geographic & Architectural Overview</h3>
<p>Ghana''s oldest mosque, built in 1421 in the Sudanese mud-and-stick architectural style, housing an ancient 17th-century Quran.</p>
<p>Situated within the scenic environment of the <strong>Savannah Region</strong>, Larabanga Mosque represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Larabanga Mosque can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Savannah Region</li>
  <li><strong>Category:</strong> History</li>
  <li><strong>Location / Access:</strong> Larabanga Village</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Founded by Islamic scholar Ayuba following a divine dream, standing for over six centuries.</p>
<p>Spanning multiple centuries of Ghanaian history, Larabanga Mosque has served as a key economic, cultural, and spiritual anchor for communities in the Savannah Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Larabanga Mosque safeguards the living history and ecological legacy of the Savannah Region for national scholars and global visitors alike."</blockquote>', 'Larabanga Village', 'larabanga_mosque.jpg', 1),
(93, 'The Mystic Stone', 'Savannah Region', 'History', '<h3>Geographic & Architectural Overview</h3>
<p>A sacred boulder famous for returning to its original spot whenever colonial road builders moved it.</p>
<p>Situated within the scenic environment of the <strong>Savannah Region</strong>, The Mystic Stone represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to The Mystic Stone can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Savannah Region</li>
  <li><strong>Category:</strong> History</li>
  <li><strong>Location / Access:</strong> Larabanga Road</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Revered by Larabanga residents as a sacred landmark possessing protective spiritual powers.</p>
<p>Spanning multiple centuries of Ghanaian history, The Mystic Stone has served as a key economic, cultural, and spiritual anchor for communities in the Savannah Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving The Mystic Stone safeguards the living history and ecological legacy of the Savannah Region for national scholars and global visitors alike."</blockquote>', 'Larabanga Road', 'mystic_stone.jpg', 0),
(94, 'Mognori Eco-Village', 'Savannah Region', 'Culture & Heritage', '<h3>Geographic & Architectural Overview</h3>
<p>A community eco-village offering canoe safaris on the Mole River, traditional drumming, and shea butter processing.</p>
<p>Situated within the scenic environment of the <strong>Savannah Region</strong>, Mognori Eco-Village represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Mognori Eco-Village can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Savannah Region</li>
  <li><strong>Category:</strong> Culture & Heritage</li>
  <li><strong>Location / Access:</strong> 15 km from Mole Park HQ</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Established to empower local communities through eco-tourism partnerships adjacent to Mole National Park.</p>
<p>Spanning multiple centuries of Ghanaian history, Mognori Eco-Village has served as a key economic, cultural, and spiritual anchor for communities in the Savannah Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Mognori Eco-Village safeguards the living history and ecological legacy of the Savannah Region for national scholars and global visitors alike."</blockquote>', '15 km from Mole Park HQ', 'mognori_village.jpg', 0),
(95, 'Salaga Slave Market Site', 'Savannah Region', 'History', '<h3>Geographic & Architectural Overview</h3>
<p>A major historical market site that served as a central trading hub during the trans-Saharan slave trade.</p>
<p>Situated within the scenic environment of the <strong>Savannah Region</strong>, Salaga Slave Market Site represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Salaga Slave Market Site can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Savannah Region</li>
  <li><strong>Category:</strong> History</li>
  <li><strong>Location / Access:</strong> Salaga Town</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Flourished in the 18th and 19th centuries as the primary market for captives, gold, and kola nuts.</p>
<p>Spanning multiple centuries of Ghanaian history, Salaga Slave Market Site has served as a key economic, cultural, and spiritual anchor for communities in the Savannah Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Salaga Slave Market Site safeguards the living history and ecological legacy of the Savannah Region for national scholars and global visitors alike."</blockquote>', 'Salaga Town', 'salaga_market.jpg', 0),
(96, 'Gambaga Escarpment', 'North East Region', 'Nature & Wildlife', '<h3>Geographic & Architectural Overview</h3>
<p>A dramatic cliffside geological formation bordering the Volta River basin with panoramic valley views.</p>
<p>Situated within the scenic environment of the <strong>North East Region</strong>, Gambaga Escarpment represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Gambaga Escarpment can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> North East Region</li>
  <li><strong>Category:</strong> Nature & Wildlife</li>
  <li><strong>Location / Access:</strong> Gambaga Mountain Ridge</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Formed the historical northern boundary of the Mamprusi Kingdom.</p>
<p>Spanning multiple centuries of Ghanaian history, Gambaga Escarpment has served as a key economic, cultural, and spiritual anchor for communities in the North East Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Gambaga Escarpment safeguards the living history and ecological legacy of the North East Region for national scholars and global visitors alike."</blockquote>', 'Gambaga Mountain Ridge', 'gambaga_escarpment.jpg', 0),
(97, 'Naa Gbewaa Shrine', 'North East Region', 'Culture & Heritage', '<h3>Geographic & Architectural Overview</h3>
<p>A sacred shrine dedicated to Naa Gbewaa, the legendary founder of the Mole-Dagbani ethnic kingdoms.</p>
<p>Situated within the scenic environment of the <strong>North East Region</strong>, Naa Gbewaa Shrine represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Naa Gbewaa Shrine can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> North East Region</li>
  <li><strong>Category:</strong> Culture & Heritage</li>
  <li><strong>Location / Access:</strong> Pusiga District</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Spiritual pilgrimage site for Dagombas, Mamprusis, and Nanumbas honoring their ancestral roots.</p>
<p>Spanning multiple centuries of Ghanaian history, Naa Gbewaa Shrine has served as a key economic, cultural, and spiritual anchor for communities in the North East Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Naa Gbewaa Shrine safeguards the living history and ecological legacy of the North East Region for national scholars and global visitors alike."</blockquote>', 'Pusiga District', 'naa_gbewaa_shrine.jpg', 0),
(98, 'Kyabobo National Park', 'Oti Region', 'Nature & Wildlife', '<h3>Geographic & Architectural Overview</h3>
<p>A rugged mountain national park featuring Mount Dzebobo, hidden waterfalls, and diverse wildlife along the Togo border.</p>
<p>Situated within the scenic environment of the <strong>Oti Region</strong>, Kyabobo National Park represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Kyabobo National Park can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Oti Region</li>
  <li><strong>Category:</strong> Nature & Wildlife</li>
  <li><strong>Location / Access:</strong> Nkwanta, Togo Border</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Established in 1997 to conserve mountainous rainforest habitats and rare species.</p>
<p>Spanning multiple centuries of Ghanaian history, Kyabobo National Park has served as a key economic, cultural, and spiritual anchor for communities in the Oti Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Kyabobo National Park safeguards the living history and ecological legacy of the Oti Region for national scholars and global visitors alike."</blockquote>', 'Nkwanta, Togo Border', 'kyabobo_park.jpg', 0),
(99, 'Breast Mountains', 'Oti Region', 'Nature & Wildlife', '<h3>Geographic & Architectural Overview</h3>
<p>Twin mountain peaks named for their symmetrical breast-like natural shape.</p>
<p>Situated within the scenic environment of the <strong>Oti Region</strong>, Breast Mountains represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Breast Mountains can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Oti Region</li>
  <li><strong>Category:</strong> Nature & Wildlife</li>
  <li><strong>Location / Access:</strong> Nkwanta Range</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Prominent natural landmark in the Nkwanta landscape.</p>
<p>Spanning multiple centuries of Ghanaian history, Breast Mountains has served as a key economic, cultural, and spiritual anchor for communities in the Oti Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Breast Mountains safeguards the living history and ecological legacy of the Oti Region for national scholars and global visitors alike."</blockquote>', 'Nkwanta Range', 'breast_mountains.jpg', 0),
(100, 'Chaiso Forest Reserve', 'Oti Region', 'Nature & Wildlife', '<h3>Geographic & Architectural Overview</h3>
<p>A dense forest reserve popular for bamboo forest walks, hiking, and bird watching.</p>
<p>Situated within the scenic environment of the <strong>Oti Region</strong>, Chaiso Forest Reserve represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Chaiso Forest Reserve can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Oti Region</li>
  <li><strong>Category:</strong> Nature & Wildlife</li>
  <li><strong>Location / Access:</strong> Jasikan District</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Preserved to protect bamboo groves and local river catchments.</p>
<p>Spanning multiple centuries of Ghanaian history, Chaiso Forest Reserve has served as a key economic, cultural, and spiritual anchor for communities in the Oti Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Chaiso Forest Reserve safeguards the living history and ecological legacy of the Oti Region for national scholars and global visitors alike."</blockquote>', 'Jasikan District', 'chaiso_forest.jpg', 0),
(101, 'Anwhiaso Pedestalled Rock', 'Western North Region', 'Nature & Wildlife', '<h3>Geographic & Architectural Overview</h3>
<p>A naturally eroded mushroom rock monument shaped like a traditional Ghanaian royal stool.</p>
<p>Situated within the scenic environment of the <strong>Western North Region</strong>, Anwhiaso Pedestalled Rock represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Anwhiaso Pedestalled Rock can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Western North Region</li>
  <li><strong>Category:</strong> Nature & Wildlife</li>
  <li><strong>Location / Access:</strong> Sefwi Anwhiaso, near Wiawso</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Geological wonder revered by local Sefwi elders as a natural throne.</p>
<p>Spanning multiple centuries of Ghanaian history, Anwhiaso Pedestalled Rock has served as a key economic, cultural, and spiritual anchor for communities in the Western North Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Anwhiaso Pedestalled Rock safeguards the living history and ecological legacy of the Western North Region for national scholars and global visitors alike."</blockquote>', 'Sefwi Anwhiaso, near Wiawso', 'anwhiaso_rock.jpg', 0),
(102, 'Bia National Park', 'Western North Region', 'Nature & Wildlife', '<h3>Geographic & Architectural Overview</h3>
<p>A UNESCO Biosphere Reserve created in 1935, protecting virgin rainforest and some of West Africa''s tallest trees.</p>
<p>Situated within the scenic environment of the <strong>Western North Region</strong>, Bia National Park represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Bia National Park can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Western North Region</li>
  <li><strong>Category:</strong> Nature & Wildlife</li>
  <li><strong>Location / Access:</strong> Bia District</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Protects endangered forest elephants, chimps, and old-growth timber reserves.</p>
<p>Spanning multiple centuries of Ghanaian history, Bia National Park has served as a key economic, cultural, and spiritual anchor for communities in the Western North Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Bia National Park safeguards the living history and ecological legacy of the Western North Region for national scholars and global visitors alike."</blockquote>', 'Bia District', 'bia_national_park.jpg', 0),
(103, 'Sefwi Wiawso Tree-Planting Canopy', 'Western North Region', 'Nature & Wildlife', '<h3>Geographic & Architectural Overview</h3>
<p>An agro-forestry eco-trail showcasing traditional tree cultivation and forest restoration.</p>
<p>Situated within the scenic environment of the <strong>Western North Region</strong>, Sefwi Wiawso Tree-Planting Canopy represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Sefwi Wiawso Tree-Planting Canopy can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Western North Region</li>
  <li><strong>Category:</strong> Nature & Wildlife</li>
  <li><strong>Location / Access:</strong> Sefwi Wiawso Hillside</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Community eco-initiative promoting rainforest reforestation in Sefwi hills.</p>
<p>Spanning multiple centuries of Ghanaian history, Sefwi Wiawso Tree-Planting Canopy has served as a key economic, cultural, and spiritual anchor for communities in the Western North Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Sefwi Wiawso Tree-Planting Canopy safeguards the living history and ecological legacy of the Western North Region for national scholars and global visitors alike."</blockquote>', 'Sefwi Wiawso Hillside', 'sefwi_canopy.jpg', 0),
(104, 'Mim Bour (Mim Rocks)', 'Ahafo Region', 'Nature & Wildlife', '<h3>Geographic & Architectural Overview</h3>
<p>Dramatic granite inselbergs rising out of teak forests, offering climbing routes and panoramic views.</p>
<p>Situated within the scenic environment of the <strong>Ahafo Region</strong>, Mim Bour (Mim Rocks) represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Mim Bour (Mim Rocks) can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Ahafo Region</li>
  <li><strong>Category:</strong> Nature & Wildlife</li>
  <li><strong>Location / Access:</strong> Mim Town, near Goaso</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Historical lookout point used by hunters and warriors in the Ahafo region.</p>
<p>Spanning multiple centuries of Ghanaian history, Mim Bour (Mim Rocks) has served as a key economic, cultural, and spiritual anchor for communities in the Ahafo Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Mim Bour (Mim Rocks) safeguards the living history and ecological legacy of the Ahafo Region for national scholars and global visitors alike."</blockquote>', 'Mim Town, near Goaso', 'mim_rocks.jpg', 0),
(105, 'Mim Lake', 'Ahafo Region', 'Nature & Waterways', '<h3>Geographic & Architectural Overview</h3>
<p>A quiet man-made reservoir used for recreational canoeing and weekend eco-relaxation.</p>
<p>Situated within the scenic environment of the <strong>Ahafo Region</strong>, Mim Lake represents a primary focal point in Ghana''s national tourism portfolio. The attraction features pristine natural topography, architectural infrastructure, and visitor facilities tailored for international scholars, eco-tourists, and cultural researchers alike.</p>

<h3>Key Attributes & Visiting Information</h3>
<p>Visitors to Mim Lake can enjoy guided walking tours, panoramic lookout points, and educational exhibits illustrating the environmental biodiversity and heritage value of the region.</p>

<ul>
  <li><strong>Region:</strong> Ahafo Region</li>
  <li><strong>Category:</strong> Nature & Waterways</li>
  <li><strong>Location / Access:</strong> Mim Suburbs, Ahafo</li>
  <li><strong>Conservation Status:</strong> Protected National Tourism Heritage Asset</li>
</ul>', '<h3>Historical Origins & Cultural Significance</h3>
<p>Created for timber processing operations, evolving into a community recreational lake.</p>
<p>Spanning multiple centuries of Ghanaian history, Mim Lake has served as a key economic, cultural, and spiritual anchor for communities in the Ahafo Region. Historically connected with traditional chieftaincy institutions, regional trade routes, and national conservation decrees, it remains an irreplaceable component of West Africa''s heritage landscape.</p>

<blockquote class="dt-quote">"Preserving Mim Lake safeguards the living history and ecological legacy of the Ahafo Region for national scholars and global visitors alike."</blockquote>', 'Mim Suburbs, Ahafo', 'mim_lake.jpg', 0);


-- HOTELS SEED DATA (MAPPED 1-TO-MANY TO THE DESTINATIONS ABOVE)

INSERT INTO `hotels` (`destination_id`, `name`, `description`, `price_per_night`, `room_capacity`, `contact_phone`, `contact_email`, `image`) VALUES
-- Greater Accra Hotels
(1, 'Mövenpick Ambassador Hotel Accra', '5-star luxury hotel in central Accra featuring lush gardens, outdoor pool, and fine dining.', 180.00, 2, '+233 30 261 1000', 'reservations.accra@movenpick.com', 'movenpick.jpg'),
(1, 'Mantra Hotel Accra', 'Modern boutique hotel minutes away from Kwame Nkrumah Memorial Park.', 75.00, 2, '+233 24 555 0101', 'stay@mantraaccra.com', 'mantra_hotel.jpg'),
(2, 'Kempinski Hotel Gold Coast City', 'Ultra-luxury 5-star hotel near Independence Square with spa, infinity pool, and high-end suites.', 220.00, 2, '+233 24 243 6000', 'reservations.accra@kempinski.com', 'kempinski.jpg'),
(2, 'La Villa Boutique Hotel', 'Chic boutique oasis in Osu featuring poolside dining and serene garden rooms.', 95.00, 2, '+233 30 278 1956', 'info@lavillaghana.com', 'la_villa.jpg'),
(3, 'Olma Colonial Suites', 'Restored colonial style apartment suites right near Osu Beachfront.', 110.00, 3, '+233 30 279 8899', 'booking@olmasuites.com', 'olma_suites.jpg'),
(3, 'Roots Apartment Hotel', 'Contemporary self-catering hotel in the heart of vibrant Osu.', 85.00, 2, '+233 30 274 2580', 'info@rootshotel.com', 'roots_hotel.jpg'),
(4, 'Ussher Fort Guest House', 'Budget heritage rest house overlooking old Jamestown harbor.', 30.00, 2, '+233 24 333 4455', 'ussherguesthouse@gmail.com', 'ussher_guesthouse.jpg'),
(4, 'Jamestown Cafe & Transit Rooms', 'Artistic guesthouse and cafe popular with culture travelers.', 35.00, 2, '+233 20 123 4567', 'stay@jamestowncafe.com', 'jamestown_cafe.jpg'),
(5, 'Accra City Hotel', '4-star hotel in Adabraka within walking distance to National Museum.', 120.00, 2, '+233 30 263 3863', 'info@accracityhotel.com', 'accra_city_hotel.jpg'),
(6, 'Cantonments Luxury Suites', 'High-end serviced apartments in Cantonments near Du Bois Center.', 140.00, 4, '+233 30 277 8899', 'reservations@cantonmentssuites.com', 'cantonments_suites.jpg'),
(6, 'Alisa Hotel North Ridge', 'Premier conference hotel featuring swimming pools and tennis courts.', 130.00, 2, '+233 30 221 4244', 'info@alisahotels.com', 'alisa_hotel.jpg'),
(7, 'Midindi Hotel', 'Boutique hotel providing quick access to central markets.', 70.00, 2, '+233 30 277 5580', 'stay@midindihotel.com', 'midindi_hotel.jpg'),
(8, 'Labadi Beach Hotel', 'Ghana\'s flagship 5-star beachfront resort featuring private beach access and dining.', 190.00, 2, '+233 30 277 2501', 'labadi@labadibeachhotel.com', 'labadi_hotel.jpg'),
(8, 'Alora Beach Resort', 'Luxury beachfront resort with oceanview lounges.', 150.00, 2, '+233 50 111 2233', 'info@alorabeach.com', 'alora_resort.jpg'),
(9, 'Bojo Beach Resort', 'Directly located on-site across lagoon with waterfront rooms.', 80.00, 2, '+233 30 291 1907', 'info@bojobeachresort.com', 'bojo_hotel.jpg'),
(10, 'Kokrobite Garden Hotel', 'Tropical garden hotel near the beach with Italian restaurant.', 45.00, 2, '+233 24 498 4880', 'kokrobitegarden@gmail.com', 'kokrobite_garden.jpg'),
(10, 'Big Milly\'s Backyard', 'Famous beachfront lodge with live reggae music and backpacker vibes.', 35.00, 2, '+233 30 271 2838', 'bigmillys@gmail.com', 'big_millys.jpg'),
(11, 'Fiesta Royale Hotel', '4-star hotel near Achimota forest featuring luxury suites.', 140.00, 2, '+233 30 274 0811', 'info@fiestaroyalehotel.com', 'fiesta_royale.jpg'),
(12, 'University of Ghana Guest House', 'Peaceful green guesthouse inside Legon campus.', 50.00, 2, '+233 30 250 0381', 'guesthouse@ug.edu.gh', 'ug_guesthouse.jpg'),
(13, 'Shai Hills Luxury Eco-Lodge', 'Scenic eco-lodge nestled near the hills of the game reserve.', 90.00, 2, '+233 24 000 8899', 'info@shaihillslodge.com', 'shai_lodge.jpg'),
(13, 'The Stone Lodge Asutsuare', 'Country resort featuring swimming pool and quiet hill views.', 70.00, 3, '+233 24 431 3411', 'info@stonelodgeghana.com', 'stone_lodge.jpg'),
(14, 'Treasure Island Resort Ada', 'On-site luxury resort with river pool and water sports.', 120.00, 4, '+233 54 000 1122', 'info@treasureislandada.com', 'treasure_island_resort.jpg'),
(14, 'Aqua Safari Resort', 'Top luxury riverfront resort across Ada estuary.', 160.00, 2, '+233 30 298 3333', 'reservations@aquasafariresort.com', 'aqua_safari.jpg'),
(15, 'Rising Phoenix Eco-Hotel', 'Cliffside eco-hotel overlooking old Accra coastline.', 40.00, 2, '+233 24 467 8900', 'risingphoenix@gmail.com', 'rising_phoenix.jpg'),
(16, 'Royal Ravico Hotel Teshie', 'Comfortable beachside hotel in Teshie-Nungua area.', 60.00, 2, '+233 30 271 3344', 'royalravico@gmail.com', 'royal_ravico.jpg'),
(17, 'Teshie Beach Guest House', 'Cozy guest lodge near carpentry workshops and beach.', 35.00, 2, '+233 24 567 8901', 'teshiebeachguesthouse@gmail.com', 'teshie_guesthouse.jpg'),

-- Ashanti Region Hotels
(18, 'Golden Tulip Kumasi City', '4-star luxury hotel in central Kumasi with lush gardens.', 130.00, 2, '+233 32 208 3700', 'info@goldentulipkumasicity.com', 'golden_tulip_kumasi.jpg'),
(18, 'Sir Max Hotel', 'Comfortable business hotel near Manhyia Palace.', 65.00, 2, '+233 32 204 4555', 'info@sirmaxhotel.com', 'sir_max_hotel.jpg'),
(19, 'The Wild Gecko Resort', 'Waterfront resort overlooking Lake Bosomtwe.', 60.00, 2, '+233 24 321 0000', 'wildgecko@gmail.com', 'wild_gecko.jpg'),
(19, 'Lake Bosomtwe Paradise Resort', 'Scenic lakefront lodge offering boat rides and serene views.', 70.00, 3, '+233 32 209 1122', 'info@paradiseresort.gh', 'bosomtwe_paradise.jpg'),
(20, 'Hotel Georgia Kumasi', 'Historic Kumasi hotel offering dining and conference halls.', 75.00, 2, '+233 32 202 3915', 'hotelgeorgia@gmail.com', 'hotel_georgia.jpg'),
(21, 'Golden Bean Hotel', 'Premier boutique hotel near Okomfo Anokye Sword site.', 110.00, 2, '+233 32 208 8000', 'info@goldenbeanhotel.com', 'golden_bean.jpg'),
(22, 'Ejisu Grace Court Hotel', 'Cozy hotel near Bonwire Kente weaving village.', 45.00, 2, '+233 24 777 8899', 'gracecourt@gmail.com', 'grace_court.jpg'),
(23, 'Adanwomase Community Eco-Guest House', 'Rustic village eco-lodge hosted by local weaving community.', 25.00, 2, '+233 24 111 9900', 'adanwomaseeco@gmail.com', 'adanwomase_guesthouse.jpg'),
(24, 'Gyamfi Hotel Ntonso', 'Local guesthouse near Adinkra stamping craft village.', 30.00, 2, '+233 24 222 1100', 'gyamfihotel@gmail.com', 'gyamfi_hotel.jpg'),
(25, 'Royal Basin Resort', '4-star garden resort a short drive from Ahwiaa.', 85.00, 2, '+233 32 206 0144', 'royalbasin@gmail.com', 'royal_basin.jpg'),
(26, 'Bobiri Forest Eco-Lodge', 'Rustic guesthouse located right inside the butterfly sanctuary.', 35.00, 2, '+233 24 333 2211', 'bobirilodge@gmail.com', 'bobiri_lodge.jpg'),
(27, 'Asantewaa Premier Guesthouse', 'Heritage guesthouse named after Yaa Asantewaa near Kumasi Zoo.', 50.00, 2, '+233 32 203 5566', 'asantewaapremier@gmail.com', 'asantewaa_guesthouse.jpg'),
(31, 'Cofkans Hotel Obuasi', 'Modern hotel in Obuasi town with swimming pool.', 65.00, 2, '+233 32 242 0500', 'cofkanshotel@gmail.com', 'cofkans_hotel.jpg'),

-- Central Region Hotels
(32, 'The Rainforest Lodge Kakum', 'Serene eco-lodge located right at the edge of Kakum National Park.', 40.00, 2, '+233 24 111 2233', 'stay@rainforestlodge.gh', 'rainforest_lodge.jpg'),
(32, 'Hans Cottage Botel', 'Famous botel built over a crocodile pond near Kakum.', 55.00, 2, '+233 33 213 2260', 'hansbotel@gmail.com', 'hans_cottage.jpg'),
(33, 'Ridge Royal Hotel Cape Coast', 'Luxury hilltop hotel overlooking Cape Coast coastline.', 120.00, 2, '+233 33 213 8000', 'info@ridgeroyalhotel.com', 'ridge_royal.jpg'),
(33, 'Cape Coast Beach Resort', 'Beachfront hotel offering ocean views and fresh seafood.', 55.00, 2, '+233 33 210 5050', 'reservations@capecoastbeachresort.com', 'cape_coast_resort.jpg'),
(34, 'Elmina Beach Resort', '4-star beachfront resort overlooking Atlantic Ocean.', 90.00, 2, '+233 33 213 3333', 'info@elminabeachresort.com', 'elmina_beach_resort.jpg'),
(34, 'Golden Hill Parker Hotel', 'Hilltop boutique hotel with panoramic views of Elmina Castle.', 80.00, 2, '+233 33 213 9999', 'info@goldenhillparker.com', 'golden_hill_parker.jpg'),
(35, 'One Africa Eco-Lodge', 'Beachfront eco-lodge near Elmina hilltops.', 45.00, 2, '+233 24 400 1122', 'oneafrica@gmail.com', 'one_africa.jpg'),
(36, 'Assin Manso Heritage Rest Stop', 'Clean rest house near Slave River site.', 25.00, 2, '+233 24 555 4433', 'assinmansorest@gmail.com', 'assin_manso_rest.jpg'),
(37, 'Mankessim Beach Resort', 'Coastal resort near Mankessim Posuban shrines.', 50.00, 2, '+233 33 209 8877', 'mankessimresort@gmail.com', 'mankessim_resort.jpg'),
(38, 'Brenu Beach Lodge', 'Secluded beach lodge right on Brenu white sand shoreline.', 45.00, 2, '+233 24 333 8899', 'brenubeach@gmail.com', 'brenu_lodge.jpg'),
(39, 'Windy Lodge Beach Resort', 'Oceanfront lodge in Winneba near Ramsar site.', 50.00, 2, '+233 33 232 2000', 'windylodge@gmail.com', 'windy_lodge.jpg'),
(40, 'Beyin Eco-Lodge', 'Footsteps away from Fort Apollonia and stilt village dock.', 35.00, 2, '+233 24 111 0099', 'beyineco@gmail.com', 'beyin_ecolodge.jpg'),
(41, 'Anomabo Beach Resort', 'Charming beachfront resort near Fort Amsterdam.', 75.00, 2, '+233 33 219 2000', 'info@anomabobeachresort.com', 'anomabo_resort.jpg'),

-- Western Region Hotels
(43, 'Amansuri Eco-Lodge Beyin', 'Landing dock eco-lodge for canoe rides to Nzulezo.', 40.00, 2, '+233 24 666 7788', 'amansurieco@gmail.com', 'amansuri_lodge.jpg'),
(44, 'Busua Beach Resort', 'Flagship surfing beach resort in Busua village.', 70.00, 2, '+233 31 209 2600', 'info@busuabeach.com', 'busua_resort.jpg'),
(44, 'Ahanta Eco-Lodge', 'Cozy eco-friendly surfing lodge right on Busua sand.', 45.00, 2, '+233 24 555 6677', 'ahantaeco@gmail.com', 'ahanta_lodge.jpg'),
(45, 'Ankasa Park Campsite & French Lodge', 'Forest lodge and eco-tents at Ankasa frontier.', 35.00, 2, '+233 24 222 4455', 'ankasacamp@gmail.com', 'ankasa_lodge.jpg'),
(46, 'Green Turtle Lodge Akwidaa', 'Famous solar-powered beach eco-lodge near Dixcove.', 40.00, 2, '+233 24 333 5566', 'greenturtle@gmail.com', 'green_turtle.jpg'),
(47, 'Lou Moon Resort Axim', 'Luxury boutique cove resort with private beach bay.', 190.00, 2, '+233 24 432 5555', 'reservations@loumoonresort.com', 'lou_moon.jpg'),
(47, 'Axim Beach Resort', 'Clifftop wooden chalets overlooking rocky Axim coast.', 65.00, 2, '+233 31 209 2233', 'aximbeachresort@gmail.com', 'axim_resort.jpg'),
(48, 'Best Western Plus Atlantic Hotel Takoradi', '4-star luxury hotel in Sekondi-Takoradi twin city.', 130.00, 2, '+233 31 202 3900', 'info@atlantichoteltakoradi.com', 'atlantic_takoradi.jpg'),
(49, 'Fort Fredericksburg Guest Rooms', 'Rustic historic rooms managed right inside the German fort.', 30.00, 2, '+233 24 111 3322', 'fredericksburgfort@gmail.com', 'fredericksburg_rooms.jpg'),
(50, 'Escape 3 Points Eco-Lodge', 'Solar-powered rustic beach cabins at Ghana\'s southernmost tip.', 35.00, 2, '+233 24 666 4433', 'escape3points@gmail.com', 'escape_3points.jpg'),
(51, 'Hideout Lodge Butre', 'Quiet beachfront chalets near Butre crocodile river.', 40.00, 2, '+233 24 555 1122', 'hideoutbutre@gmail.com', 'hideout_butre.jpg'),

-- Northern Region Hotels
(52, 'Modern City Hotel Tamale', 'Premier luxury hotel in Tamale with pool and gardens.', 85.00, 2, '+233 37 202 2400', 'info@moderncityhotel.com', 'modern_city_tamale.jpg'),
(52, 'Picorna Hotel Tamale', 'Centrally located hotel in Tamale city.', 50.00, 2, '+233 37 202 2722', 'picornahotel@gmail.com', 'picorna_hotel.jpg'),
(53, 'Radach Lodge & Conference Centre', 'Spacious lodge near Tamale Central Mosque.', 60.00, 2, '+233 37 202 3211', 'radachlodge@gmail.com', 'radach_lodge.jpg'),
(54, 'Daboya District Assembly Guest House', 'Riverside guesthouse in Daboya weaving village.', 25.00, 2, '+233 24 444 3322', 'daboyaguesthouse@gmail.com', 'daboya_guesthouse.jpg'),
(55, 'Yendi Gateway Hotel', 'Hotel near Ya Naa sovereign palace in Yendi.', 35.00, 2, '+233 37 209 8800', 'yendigateway@gmail.com', 'yendi_gateway.jpg'),

-- Volta Region Hotels
(56, 'Waterfall Lodge Wli', 'Charming lodge right at the entrance of Wli Waterfalls.', 35.00, 2, '+233 24 999 1122', 'info@waterfalllodgewli.com', 'wli_waterfall_lodge.jpg'),
(56, 'Wli Water Heights Hotel', 'Mountain view hotel at the foot of Wli falls.', 30.00, 2, '+233 24 100 2200', 'wliheights@gmail.com', 'wli_heights.jpg'),
(57, 'Tagbo Falls Lodge', 'Eco-lodge located right at the base trailhead of Afadjato.', 35.00, 2, '+233 24 333 9988', 'tagbolodge@gmail.com', 'tagbo_lodge.jpg'),
(58, 'Tafi Atome Community Eco-Camp', 'Village guesthouse hosted by monkey sanctuary committee.', 20.00, 2, '+233 24 555 7766', 'tafiatomeeco@gmail.com', 'tafi_ecocamp.jpg'),
(59, 'Avatime Mountain Lodge Amedzofe', 'High altitude mountain lodge with cool weather vistas.', 50.00, 2, '+233 24 444 5511', 'avatimelodge@gmail.com', 'avatime_lodge.jpg'),
(60, 'Meet Me There African Home Lodge', 'Eco-lodge on the Keta sandbar between sea and lagoon.', 45.00, 2, '+233 24 333 1100', 'meetmethere@gmail.com', 'meet_me_there.jpg'),
(60, 'Keta Beach Hotel', 'Oceanfront hotel on historic Keta coastline.', 40.00, 2, '+233 36 209 1122', 'ketabeachhotel@gmail.com', 'keta_beach_hotel.jpg'),

-- Eastern Region Hotels
(64, 'Peduase Valley Resort Aburi', '4-star luxury resort nestled in Akwapim hills.', 150.00, 2, '+233 30 274 0200', 'info@peduasevalleyresort.com', 'peduase_valley.jpg'),
(64, 'The Hill View Hotel Aburi', 'Boutique hill hotel near Aburi Botanical Gardens.', 65.00, 2, '+233 30 290 8877', 'hillviewaburi@gmail.com', 'hill_view_aburi.jpg'),
(65, 'Mac-Dic Royal Plaza Koforidua', 'Luxury resort with large pool near Boti Falls.', 85.00, 2, '+233 34 202 8899', 'macdicroyal@gmail.com', 'macdic_royal.jpg'),
(71, 'The Royal Senchi Resort Akosombo', 'Luxury 4-star riverfront resort on Lake Volta.', 210.00, 2, '+233 30 340 9180', 'info@theroyalsenchi.com', 'royal_senchi.jpg'),
(71, 'Voltariana Hotel Akosombo', 'Scenic hilltop hotel overlooking Akosombo Dam.', 70.00, 2, '+233 30 221 8200', 'voltarianahotel@gmail.com', 'voltariana_hotel.jpg'),
(72, 'Safari Valley Luxury Cabins', 'On-site eco-park cabins at Safari Valley Resort.', 170.00, 2, '+233 30 290 9900', 'cabins@safarivalleyresort.com', 'safari_valley_cabins.jpg'),
(75, 'Bridgeview Resort Atimpoku', 'Luxury cliffside resort overlooking Adomi Bridge on Volta River.', 180.00, 2, '+233 20 000 7788', 'info@bridgeviewgh.com', 'bridgeview_resort.jpg'),

-- Upper East Region Hotels
(76, 'Kubs Lodge Paga', 'Friendly lodge near sacred Paga crocodile pond.', 35.00, 2, '+233 38 209 8899', 'kubslodge@gmail.com', 'kubs_lodge.jpg'),
(77, 'Oasis Hotel Bolgatanga', 'Premier hotel in Bolgatanga near Tongo Hills.', 45.00, 2, '+233 38 202 2233', 'oasishotelbolga@gmail.com', 'oasis_bolga.jpg'),
(78, 'SWOPA Guest Rooms Sirigu', 'Authentic traditional mud-wall decorated rooms in Sirigu village.', 25.00, 2, '+233 24 333 4411', 'swopasirigu@gmail.com', 'swopa_rooms.jpg'),

-- Upper West Region Hotels
(81, 'Uplands Hotel Wa', 'Top hotel in Wa town near Wa Naa Palace.', 50.00, 2, '+233 39 202 2211', 'uplandshotelwa@gmail.com', 'uplands_wa.jpg'),
(83, 'Wechiau Hippo Sanctuary Eco-Lodge', 'Traditional mud-thatch eco-rooms beside Black Volta River.', 25.00, 2, '+233 24 555 9900', 'wechiauhippo@gmail.com', 'wechiau_lodge.jpg'),

-- Bono East Region Hotels
(85, 'Boabeng-Fiema Community Guest House', 'On-site guesthouse inside the sacred monkey sanctuary.', 20.00, 2, '+233 24 111 5544', 'boabengfiemaeco@gmail.com', 'boabeng_guesthouse.jpg'),
(86, 'Kintampo Falls Hotel', 'Comfortable hotel near Kintampo Waterfalls.', 40.00, 2, '+233 35 209 2211', 'kintampofallshotel@gmail.com', 'kintampo_hotel.jpg'),

-- Bono Region Hotels
(89, 'Akomapa Guest House Techiman', 'Quiet lodge near Tanoboase sacred grove.', 30.00, 2, '+233 35 202 1100', 'akomapaguesthouse@gmail.com', 'akomapa_guesthouse.jpg'),

-- Savannah Region Hotels
(91, 'Mole Safari Motel', 'Iconic motel inside Mole Park overlooking elephant waterhole.', 45.00, 3, '+233 24 437 2191', 'reservations@molesafarimotel.org', 'mole_motel.jpg'),
(91, 'Zaina Lodge', '5-star luxury eco-lodge inside Mole National Park.', 250.00, 2, '+233 24 243 6300', 'info@zainalodge.com', 'zaina_lodge.jpg'),
(92, 'Savannah Lodge Larabanga', 'Traditional mud-brick lodge near 1421 ancient mosque.', 25.00, 2, '+233 24 555 7788', 'savannahlodge@gmail.com', 'savannah_lodge.jpg'),

-- North East Region Hotels
(96, 'Gambaga Rest Stop', 'Guesthouse perched near Gambaga cliffside escarpment.', 25.00, 2, '+233 24 333 7711', 'gambagarest@gmail.com', 'gambaga_rest.jpg'),

-- Oti Region Hotels
(98, 'Kyabobo Park Guest House', 'Park ranger guesthouse at Kyabobo mountain entrance.', 25.00, 2, '+233 24 444 8822', 'kyaboboguesthouse@gmail.com', 'kyabobo_guesthouse.jpg'),

-- Western North Region Hotels
(101, 'Sefwi Wiawso Executive Lodge', 'Executive lodge in Sefwi Wiawso.', 45.00, 2, '+233 31 209 1100', 'sefwilodge@gmail.com', 'sefwi_lodge.jpg'),

-- Ahafo Region Hotels
(104, 'Goaso Oasis Hotel', 'Comfortable hotel near Mim Rocks in Goaso.', 35.00, 2, '+233 35 209 4433', 'goasooasis@gmail.com', 'goaso_oasis.jpg');

-- SEED BOOKINGS & REVIEWS
INSERT INTO `bookings` (`id`, `user_id`, `hotel_id`, `check_in_date`, `check_out_date`, `guests_count`, `total_price`, `status`) VALUES
(1, 2, 1, '2026-08-10', '2026-08-12', 2, 360.00, 'Confirmed'),
(2, 3, 13, '2026-08-15', '2026-08-18', 2, 165.00, 'Pending');

INSERT INTO `reviews` (`id`, `user_id`, `destination_id`, `hotel_id`, `rating`, `comment`, `status`) VALUES
(1, 2, 1, 1, 5, 'Visiting Kwame Nkrumah Memorial Park was a deeply moving cultural experience! The Mövenpick hotel nearby was world class.', 'Approved'),
(2, 3, 32, 13, 5, 'Walking across the canopy walkway at Kakum National Park was unforgettable! The Rainforest Lodge was peaceful and clean.', 'Approved');
