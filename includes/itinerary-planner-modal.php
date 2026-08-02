<!-- includes/itinerary-planner-modal.php - AI Travel Itinerary Planner Modal -->
<div class="modal fade" id="itineraryPlannerModal" tabindex="-1" aria-labelledby="itineraryPlannerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header text-white py-3" style="background: linear-gradient(135deg, #1f2937, #10b981);">
                <h5 class="modal-title fw-bold" id="itineraryPlannerModalLabel">
                    <i class="fa-solid fa-wand-magic-sparkles text-warning me-2"></i> AI Ghanaian Travel Itinerary Planner
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-4 bg-light">
                <form id="itineraryForm" onsubmit="generateItinerary(event)">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-dark"><i class="fa-solid fa-clock text-success me-1"></i> Trip Duration</label>
                            <select id="tripDuration" class="form-select rounded-3" required>
                                <option value="1">1 Day Express Tour</option>
                                <option value="3" selected>3 Days Heritage & Safari</option>
                                <option value="5">5 Days Full Ghana Odyssey</option>
                                <option value="7">7 Days Luxury Eco Explorer</option>
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-dark"><i class="fa-solid fa-wallet text-warning me-1"></i> Budget Range</label>
                            <select id="tripBudget" class="form-select rounded-3" required>
                                <option value="budget">Budget Backpacker ($30 - $70 / day)</option>
                                <option value="moderate" selected>Comfort Explorer ($80 - $180 / day)</option>
                                <option value="luxury">Luxury VIP ($200+ / day)</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold text-dark"><i class="fa-solid fa-heart text-danger me-1"></i> Primary Interest</label>
                            <select id="tripInterest" class="form-select rounded-3" required>
                                <option value="history">Historical Forts & Castles</option>
                                <option value="nature" selected>Canopy Walks & Waterfalls</option>
                                <option value="beach">Tropical Beaches & Resorts</option>
                                <option value="culture">Cultural Heritage & Crafts</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-digitour-primary w-100 fw-bold py-2 rounded-3">
                        <i class="fa-solid fa-compass me-2"></i> Generate Custom Ghana Tour Plan
                    </button>
                </form>

                <div id="itineraryResults" class="mt-4 d-none">
                    <div class="p-3 bg-white border rounded-4 shadow-sm">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-route text-success me-2"></i> Your Customized Travel Plan</h6>
                            <span class="badge bg-success" id="planBadge">3-Day Safari & History</span>
                        </div>

                        <div id="itineraryDays" class="vstack gap-3">
                            <!-- Populated dynamically via JavaScript -->
                        </div>

                        <div class="mt-3 text-end">
                            <button onclick="window.print()" class="btn btn-sm btn-outline-dark me-2"><i class="fa-solid fa-print me-1"></i> Print Plan</button>
                            <a href="destinations.php" class="btn btn-sm btn-success fw-bold"><i class="fa-solid fa-compass me-1"></i> Explore All Sites</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function generateItinerary(e) {
    e.preventDefault();
    let duration = document.getElementById('tripDuration').value;
    let interest = document.getElementById('tripInterest').value;
    let results = document.getElementById('itineraryResults');
    let container = document.getElementById('itineraryDays');
    let badge = document.getElementById('planBadge');

    badge.innerText = duration + "-Day " + interest.toUpperCase() + " Tour";
    container.innerHTML = '';

    let plans = {
        history: [
            { day: "Day 1: Greater Accra Heritage", site: "Kwame Nkrumah Memorial Park & Independence Square", activity: "Tour original mausoleum, explore National Museum.", hotel: "Labadi Beach Hotel / Accra City Hotel" },
            { day: "Day 2: Central Region Castles", site: "Cape Coast Castle & Elmina Castle", activity: "Walk through UNESCO dungeon heritage & Door of No Return.", hotel: "Ridge Royal Hotel Cape Coast" },
            { day: "Day 3: Forest Canopy Walk", site: "Kakum National Park Canopy Walkway", activity: "350-meter canopy walk 40m high in rainforest canopy.", hotel: "Coconut Grove Beach Resort" }
        ],
        nature: [
            { day: "Day 1: Rainforest Adventure", site: "Kakum National Park & Praso River", activity: "Hike early morning birdwatching trails & canopy ropes.", hotel: "Hans Cottage Botel" },
            { day: "Day 2: Elephant Safari", site: "Mole National Park (Savannah Region)", activity: "Guided walking safari with wild elephants & baboons.", hotel: "Zaina Lodge Mole" },
            { day: "Day 3: Ancient Mud Architecture", site: "Larabanga Mosque & Mystic Stone", activity: "Photograph 15th century Sudanese architecture.", hotel: "Mole Motel" }
        ],
        beach: [
            { day: "Day 1: Accra Atlantic Coastline", site: "Labadi Beach & Osu Nightlife", activity: "Sunset coconut drinks, horseback riding, local live music.", hotel: "Royal Senchi Resort" },
            { day: "Day 2: Central Coast Sands", site: "Elmina Beach Resort & Brenu Akyinim", activity: "Sailing, beach volleyball, fresh grilled seafood.", hotel: "Coconut Grove Beach Resort" },
            { day: "Day 3: Western Palm Lagoons", site: "Busua Beach & Nzulezo Stilt Village", activity: "Surfing, canoe excursion across Lake Tadane.", hotel: "Busua Beach Resort" }
        ],
        culture: [
            { day: "Day 1: Ashanti Kingdom Heart", site: "Manhyia Palace Museum & Kejetia Market", activity: "Examine royal Ashanti regalia and vibrant textile markets.", hotel: "Golden Tulip Kumasi" },
            { day: "Day 2: Kente & Woodcraft Villages", site: "Bonwire Kente Weaving Village", activity: "Try hand-weaving genuine traditional Kente cloth.", hotel: "Noda Hotel Kumasi" },
            { day: "Day 3: Sacred Lake Bosomtwe", site: "Lake Bosomtwe Crater", activity: "Horseback riding along West Africa's ancient meteorite lake.", hotel: "Paradise Resort Bosomtwe" }
        ]
    };

    let selectedList = plans[interest] || plans.history;
    let count = Math.min(parseInt(duration), selectedList.length);

    for (let i = 0; i < count; i++) {
        let item = selectedList[i];
        container.innerHTML += `
            <div class="p-3 bg-light rounded-3 border-start border-4 border-success">
                <div class="fw-bold text-success mb-1"><i class="fa-solid fa-calendar-day me-1"></i> ${item.day}</div>
                <div class="fw-bold text-dark fs-6 mb-1">${item.site}</div>
                <p class="text-muted small mb-2">${item.activity}</p>
                <div class="badge bg-warning text-dark"><i class="fa-solid fa-hotel me-1"></i> Suggested Stay: ${item.hotel}</div>
            </div>
        `;
    }

    results.classList.remove('d-none');
}
</script>
