

const defaultImagePath = 'assets/default_leader.png';
const defaultUserImagePath = 'assets/default_user.png'; 

console.log("Citizen Dashboard JS (v5 - Direct Execution) loaded.");

async function fetchTrendingNews() {
    console.log("Fetching trending news via PHP backend...");
    $('#news-hero-section').html('<div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted">Fetching the latest political trends...</p>');

    try {
        const response = await fetch('api/fetch_trending_news.php', {
            method: 'GET',
            headers: { 'Content-Type': 'application/json' },
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();
        
        if (result.success && result.news) {
            const newsHtml = result.news;
            const styledHtml = `
                <div class="alert alert-info text-start" role="alert">
                    <h5 class="alert-heading"><i class="fas fa-newspaper me-2"></i>Trending Political News</h5>
                    ${newsHtml}
                </div>
            `;
            $('#news-hero-section').html(styledHtml);
        } else {
            throw new Error(result.error || "Invalid API response structure.");
        }
    } catch (error) {
        console.error("Error fetching trending news:", error);
        $('#news-hero-section').html(`<div class="alert alert-warning">Could not fetch news at this time. ${error.message}</div>`);
    }
}
fetchTrendingNews();
function renderStars(rating, size = 'sm') {
    let starsHtml = '';
    const roundedRating = Math.floor(rating * 2) / 2;
    const starClass = size === 'lg' ? 'fs-4' : '';
    for (let i = 1; i <= 5; i++) {
        if (i <= roundedRating) starsHtml += `<i class="fas fa-star text-warning ${starClass}"></i>`;
        else if (i - 0.5 === roundedRating) starsHtml += `<i class="fas fa-star-half-alt text-warning ${starClass}"></i>`;
        else starsHtml += `<i class="far fa-star text-warning ${starClass}"></i>`;
    }
    return starsHtml;
}
function timeAgo(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    const seconds = Math.floor((new Date() - date) / 1000);
    let interval = seconds / 31536000;
    if (interval > 1) return Math.floor(interval) + " years ago";
    interval = seconds / 2592000;
    if (interval > 1) return Math.floor(interval) + " months ago";
    interval = seconds / 86400;
    if (interval > 1) return Math.floor(interval) + " days ago";
    interval = seconds / 3600;
    if (interval > 1) return Math.floor(interval) + " hours ago";
    interval = seconds / 60;
    if (interval > 1) return Math.floor(interval) + " minutes ago";
    return "just now";
}

// --- Leader Search ---
function searchLeaders() {
    console.log("searchLeaders() called...");
    const formData = $('#leader-search-form').serialize();
    $('#leader-results').html('<div class="col-12 text-center p-5"><div class="spinner-border text-primary" role="status"></div><p>Searching for leaders...</p></div>');
    $.ajax({
        url: 'api/search_leaders.php',
        type: 'GET', data: formData, dataType: 'json',
        success: function(leaders) {
            let html = '';
            if (leaders && leaders.length > 0) {
                leaders.forEach(leader => {
                    const imageSrc = leader.photo_path && leader.photo_path.trim() !== '' ? leader.photo_path : defaultImagePath;
                    html += `
                        <div class="col-md-4 mb-4">
                            <div class="card shadow-sm leader-card h-100">
                                <img src="${imageSrc}" class="card-img-top" alt="${leader.full_name}">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title text-primary">${leader.full_name}</h5>
                                    <p class="card-text flex-grow-1">
                                        <strong>Position:</strong> ${leader.current_position || 'N/A'}<br>
                                        <strong>Party:</strong> ${leader.party_affiliation || 'N/A'}<br>
                                    </p>
                                    <div class="mb-2">
                                        ${renderStars(leader.average_rating)}
                                        <span class="ms-1 text-muted small">(${leader.total_ratings})</span>
                                    </div>
                                    <button class="btn btn-sm btn-outline-primary view-details-btn mt-auto" data-id="${leader.id}">View Full Profile</button>
                                </div>
                            </div>
                        </div>`;
                });
                $('#leader-results').html(html);
            } else {
                $('#leader-results').html('<div class="col-12 text-center text-muted p-5">No leaders found matching your criteria.</div>');
            }
        },
        error: function(jqXHR) {
            console.error("Search failed:", jqXHR.responseText);
            $('#leader-results').html('<div class="col-12 text-center text-danger p-5">An error occurred during the search.</div>');
        }
    });
}
$('#leader-search-form').on('submit', function(e) { e.preventDefault(); searchLeaders(); });
$('#leader-search-form').on('reset', function() { window.location.reload(); });

// --- View Full Profile Modal ---
$(document).on('click', '.view-details-btn', function() {
    const leaderId = $(this).data('id');
    const modal = $('#leaderProfileModal');
    modal.modal('show');
    modal.data('leader-id', leaderId); 
    modal.find('.modal-body').html('<div class="text-center p-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Fetching full profile...</p></div>');

    $.ajax({
        url: 'api/get_leader.php', type: 'GET', data: { id: leaderId }, dataType: 'json',
        success: function(response) {
            if (response.error) {
                modal.find('.modal-body').html(`<div class="alert alert-danger m-3">${response.error}</div>`); return;
            }
            if (!response.details || !response.details.id) {
                modal.find('.modal-body').html(`<div class="alert alert-danger m-3">File Mismatch Error.</div>`); return;
            }

            const leader = response.details;
            const summary = response.review_summary;
            const reviews = response.reviews_list;
            const user_review = response.user_review;
            const imageSrc = leader.photo_path && leader.photo_path.trim() !== '' ? leader.photo_path : defaultImagePath;
            
            let ratingBreakdownHtml = '';
            for (let i = 5; i >= 1; i--) {
                const percent = summary.breakdown[i] || 0;
                ratingBreakdownHtml += `
                    <div class="row g-0 align-items-center mb-1">
                        <div class="col-1">${i}</div>
                        <div class="col-1"><i class="fas fa-star text-warning small"></i></div>
                        <div class="col-8">
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-warning" role="progressbar" style="width: ${percent}%;" aria-valuenow="${percent}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                        <div class="col-2 text-end small text-muted">${Math.round(percent)}%</div>
                    </div>`;
            }

            let userReviewHtml = `
                <h5 class="mt-4 mt-lg-0">Your Review</h5>
                <p class="text-muted small">Share your experience to help others.</p>
                <div id="user-rating-stars" class="mb-2" data-rating="${user_review?.rating || 0}">
                    ${[1, 2, 3, 4, 5].map(i => `<i class="far fa-star star-input" data-value="${i}" style="cursor: pointer; font-size: 1.5rem; margin-right: 5px;"></i>`).join('')}
                </div>
                <textarea id="review-text-input" class="form-control" rows="3" placeholder="Write your review here (optional)...">${user_review?.review_text || ''}</textarea>
                <button id="submit-review-btn" class="btn btn-primary btn-sm mt-2">Submit Review</button>
                <div id="review-feedback" class="small mt-2"></div>`;

            let allReviewsHtml = `<h5 class="mt-4">All Reviews (${reviews.length})</h5><hr class="mt-2 mb-3">`;
            if (reviews.length > 0) {
                reviews.forEach(review => {
                    allReviewsHtml += `
                        <div class="d-flex mb-3">
                            <div class="flex-shrink-0">
                                <img src="${defaultUserImagePath}" class="rounded-circle" alt="${review.full_name}" width="40" height="40">
                            </div>
                            <div class="ms-3">
                                <h6 class="mb-0">${review.full_name}</h6>
                                <div class="mb-1">${renderStars(review.rating)}</div>
                                <p class="mb-0 small">${review.review_text || ''}</p>
                                <small class="text-muted">${timeAgo(review.updated_at)}</small>
                            </div>
                        </div>`;
                });
            } else {
                allReviewsHtml += '<p class="text-muted">No written reviews yet. Be the first!</p>';
            }

            const profileHtml = `
                <div class="profile-header">
                    <button type="button" class="btn-close btn-close-white profile-close-btn" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="container-fluid">
                        <div class="row align-items-center">
                            <div class="col-lg-2 col-md-3 text-center text-md-start mb-3 mb-md-0">
                                <img src="${imageSrc}" class="profile-header-img" alt="${leader.full_name}">
                            </div>
                            <div class="col-lg-6 col-md-5">
                                <h2 class="profile-header-name">${leader.full_name}</h2>
                                <p class="profile-header-title">${leader.current_position || 'N/A'}</p>
                            </div>
                            <div class="col-lg-4 col-md-4">
                                <div class="profile-quick-facts">
                                    <h6 class="text-white-50 mb-2">Quick Facts</h6>
                                    <p><strong>Party:</strong> ${leader.party_affiliation || 'N/A'}</p>
                                    <p><strong>Constituency:</strong> ${leader.constituency || 'N/A'}</p>
                                    <p><strong>Career:</strong> ${leader.career_duration || 'N/A'} years</p>
                                    <p><strong>Rating:</strong> ${renderStars(summary.average_rating)} (${summary.total_ratings})</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="profile-body">
                    <ul class="nav nav-tabs profile-tabs" role="tablist">
                        <li class="nav-item" role="presentation"><button class="nav-link active" id="tab-background" data-bs-toggle="tab" data-bs-target="#content-background" type="button" role="tab">Background</button></li>
                        <li class="nav-item" role="presentation"><button class="nav-link" id="tab-financial" data-bs-toggle="tab" data-bs-target="#content-financial" type="button" role="tab">Finances & Assets</button></li>
                        <li class="nav-item" role="presentation"><button class="nav-link" id="tab-legal" data-bs-toggle="tab" data-bs-target="#content-legal" type="button" role="tab">Legal & Education</button></li>
                        <li class="nav-item" role="presentation"><button class="nav-link" id="tab-reviews" data-bs-toggle="tab" data-bs-target="#content-reviews" type="button" role="tab">Ratings & Reviews</button></li>
                    </ul>
                    <div class="tab-content p-4">
                        <div class="tab-pane fade show active" id="content-background" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5 class="mb-3">Personal Details</h5>
                                    <p><strong>Age:</strong> ${leader.age || 'N/A'} (DOB: ${leader.dob || 'N/A'})</p>
                                    <p><strong>Gender:</strong> ${leader.gender || 'N/A'}</p>
                                    <p><strong>Children:</strong> ${leader.num_children || '0'}</p>
                                </div>
                                <div class="col-md-6">
                                    <h5 class="mb-3">Career & Education</h5>
                                    <p><strong>Past Positions:</strong> ${leader.past_positions || 'N/A'}</p>
                                    <p><strong>Qualifications:</strong> ${leader.qualifications || 'N/A'}</p>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="content-financial" role="tabpanel">
                            <h5 class="mb-3">Financial Disclosures</h5>
                            <p><strong>Declared Assets:</strong> ${leader.declared_assets || 'N/A'}</p>
                            <p><strong>Annual Income:</strong> ${leader.annual_income || 'N/A'}</p>
                            <p><strong>Businesses Owned:</strong> ${leader.businesses_owned || 'None Declared'}</p>
                            <p><strong>Investments:</strong> ${leader.investments || 'None Declared'}</p>
                        </div>
                        <div class="tab-pane fade" id="content-legal" role="tabpanel">
                            <h5 class="mb-3">Legal Record</h5>
                            <p><strong>Total Police Cases:</strong> <span class="badge bg-danger">${leader.total_police_cases || 0}</span></p>
                            <p><strong>Type of Cases:</strong> ${leader.case_types || 'N/A'}</p>
                            <p><strong>Court Case Status:</strong> ${leader.court_case_status || 'N/A'}</p>
                        </div>
                        <div class="tab-pane fade" id="content-reviews" role="tabpanel">
                            <div class="row">
                                <div class="col-lg-5 border-end-lg">
                                    <h4 class="mb-3">Review Summary</h4>
                                    <div class="text-center bg-light p-3 rounded">
                                        <h1 class="display-4 fw-bold">${summary.average_rating.toFixed(1)}</h1>
                                        ${renderStars(summary.average_rating, 'lg')}
                                        <p class="text-muted mt-1 mb-0">${summary.total_ratings} Ratings</p>
                                    </div>
                                    <div class="mt-4">${ratingBreakdownHtml}</div>
                                </div>
                                <div class="col-lg-7 mt-4 mt-lg-0">
                                    <div class="ps-lg-3">
                                        ${userReviewHtml}
                                        <div id="all-reviews-container">${allReviewsHtml}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`;
            
            modal.find('.modal-body').html(profileHtml);
            const currentRating = $('#user-rating-stars').data('rating');
            updateStarDisplay(currentRating);
        },
        error: function(jqXHR) {
            modal.find('.modal-body').html('<div class="alert alert-danger m-3">Failed to connect.</div>');
            console.error("AJAX Error:", jqXHR.responseText);
        }
    });
});

// --- Star Rating Handlers ---
function updateStarDisplay(rating) {
    $('#leaderProfileModal #user-rating-stars i').each(function() {
        if ($(this).data('value') <= rating) {
            $(this).removeClass('far').addClass('fas text-warning');
        } else {
            $(this).removeClass('fas text-warning').addClass('far');
        }
    });
}
$(document).on('mouseenter', '#user-rating-stars .star-input', function() {
    updateStarDisplay($(this).data('value'));
});
$(document).on('mouseleave', '#user-rating-stars', function() {
    updateStarDisplay($(this).data('rating'));
});
$(document).on('click', '#user-rating-stars .star-input', function() {
    const rating = $(this).data('value');
    $('#user-rating-stars').data('rating', rating);
    updateStarDisplay(rating);
});

// --- Submit Review Handler ---
$(document).on('click', '#submit-review-btn', function() {
    const $button = $(this);
    const leaderId = $('#leaderProfileModal').data('leader-id');
    const rating = $('#user-rating-stars').data('rating');
    const review_text = $('#review-text-input').val();
    const feedbackEl = $('#review-feedback');

    if (rating == 0) {
        feedbackEl.text('Please select a star rating.').addClass('text-danger'); return;
    }
    $button.prop('disabled', true).text('Submitting...');
    feedbackEl.text('Submitting...').removeClass('text-danger text-success').addClass('text-muted');

    $.ajax({
        url: 'api/submit_review.php',
        type: 'POST', contentType: 'application/json',
        data: JSON.stringify({ leader_id: leaderId, rating: rating, review_text: review_text }),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                feedbackEl.text('Review saved! Refreshing...').removeClass('text-muted').addClass('text-success');
                setTimeout(() => {
                    const originalButton = $(`.view-details-btn[data-id="${leaderId}"]`);
                    originalButton.click();
                    const isSearchActive = $('#leader-search-form').serialize().replace(/[^&?]*?=&/g, '').replace(/&[^&?]*?=$/g, '').length > 0;
                    if (isSearchActive) {
                        searchLeaders();
                    } else {
                        window.location.reload();
                    }
                }, 1000);
            } else {
                feedbackEl.text(response.message || 'Could not save review.').removeClass('text-muted').addClass('text-danger');
                $button.prop('disabled', false).text('Submit Review');
            }
        },
        error: function() {
            feedbackEl.text('An error occurred.').removeClass('text-muted').addClass('text-danger');
            $button.prop('disabled', false).text('Submit Review');
        }
    });
});

// // --- 4. AI CHATBOT LOGIC (UPDATED FOR YOUR REQUIREMENTS) ---

// const $chatWindow = $('#chat-window');

// // --- THIS IS THE FIX ---
// // 1. Force the window to be hidden on page load, overriding any CSS conflicts.
// $chatWindow.hide(0); 

// // 2. Use a direct, simple click handler. This is cleaner and avoids delegation conflicts.
// // UPDATED: Always open the window on icon click (no toggling)
// $('#chat-bubble-btn').on('click', function(e) {
//     e.stopPropagation(); // Stop the click from propagating
//     console.log("Chat bubble clicked"); // For debugging
//     // CHANGED: Use fadeIn to always show the window (instead of fadeToggle)
//     $chatWindow.fadeIn(300);
// });

// // 3. Use a direct click handler for the close button.
// // UPDATED: Always close the window on close button click
// $('#chat-close-btn').on('click', function(e) {
//     e.stopPropagation(); // Stop the click from propagating
//     console.log("Chat close clicked"); // For debugging
//     // CHANGED: Use fadeOut to always hide the window
//     $chatWindow.fadeOut(300);
// });

// // Handle form submission
// // We use a direct handler here as well.
// $('#chat-form').on('submit', function(e) {
//     e.preventDefault();
//     const $input = $('#chat-input');
//     const userQuery = $input.val().trim();
    
//     if (userQuery === "") return;

//     // Add user message to chat
//     appendChatMessage(userQuery, 'user');
//     $input.val(''); // Clear input

//     // Show typing indicator
//     appendChatMessage('<div class="spinner-border spinner-border-sm" role="status"></div>', 'ai-loading');

//     // Call the AI function (now points to your PHP backend)
//     callGeminiChat(userQuery);
// });

// // Function to add a message to the chat window
// function appendChatMessage(message, type) {
//     const $chatHistory = $('#chat-history');
    
//     let finalMessage;
//     if (type === 'ai-loading') {
//         finalMessage = message; // Pass the raw HTML spinner
//     } else {
//         // Sanitize all other messages to prevent HTML injection
//         const sanitizedMessage = message.replace(/</g, "&lt;").replace(/>/g, "&gt;");
//         finalMessage = `<p>${sanitizedMessage.replace(/\n/g, '<br>')}</p>`;
//     }
    
//     const messageHtml = `<div class="chat-message ${type}">${finalMessage}</div>`;
//     $chatHistory.append(messageHtml);
//     // Scroll to bottom
//     $chatHistory.scrollTop($chatHistory[0].scrollHeight);
// }

// // Function to call your new PHP chatbot backend
// async function callGeminiChat(userQuery) {
//     console.log("Calling api/chatbot.php for: " + userQuery);

//     try {
//         const response = await fetch('api/chatbot.php', {
//             method: 'POST',
//             headers: { 'Content-Type': 'application/json' },
//             body: JSON.stringify({ query: userQuery }) // Send the query in the body
//         });

//         if (!response.ok) {
//             throw new Error(`HTTP error! status: ${response.status}`);
//         }

//         const result = await response.json();
//         let aiResponse;

//         if (result.success && result.reply) {
//             aiResponse = result.reply;
//         } else {
//             aiResponse = result.error || "Sorry, I couldn't find an answer to that.";
//         }

//         // Remove the loading spinner
//         $('#chat-history .chat-message.ai-loading').remove();
//         // Add the real AI response
//         appendChatMessage(aiResponse, 'ai-message');

//     } catch (error) {
//         console.error("Error in AI Chat:", error);
//         // Remove the loading spinner
//         $('#chat-history .chat-message.ai-loading').remove();
//         // Add an error message
//         appendChatMessage("Sorry, I'm having trouble connecting. Please try again later.", 'ai-message');
//     }
// }
