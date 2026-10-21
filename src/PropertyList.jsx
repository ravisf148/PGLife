import React, { useState, useEffect, useCallback } from "react";
import { createRoot } from "react-dom/client";

function StarRating({ rating }) {
  const stars = [];
  for (let i = 0; i < 5; i++) {
    if (rating >= i + 0.8) {
      stars.push(<i key={i} className="fas fa-star"></i>);
    } else if (rating >= i + 0.3) {
      stars.push(<i key={i} className="fas fa-star-half-alt"></i>);
    } else {
      stars.push(<i key={i} className="far fa-star"></i>);
    }
  }
  return (
    <div className="star-container" title={rating}>
      {stars}
    </div>
  );
}

function PropertyList({ initialCity }) {
  const [properties, setProperties] = useState([]);
  const [city, setCity] = useState(initialCity || "");
  const [gender, setGender] = useState("all");
  const [budget, setBudget] = useState("all");
  const [sort, setSort] = useState("none");
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  // Filter Modal local state
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [tempCity, setTempCity] = useState(initialCity || "");
  const [tempGender, setTempGender] = useState("all");
  const [tempBudget, setTempBudget] = useState("all");

  // In-flight locking for interest toggling
  const [togglingIds, setTogglingIds] = useState(new Set());

  // Fetch properties from backend API
  const fetchProperties = useCallback(async (currentCity, currentGender, currentBudget, currentSort) => {
    setLoading(true);
    setError(null);

    const spinner = document.getElementById("loading");
    if (spinner) spinner.style.display = "block";

    try {
      const params = new URLSearchParams();
      if (currentCity) params.append("city", currentCity);
      if (currentGender && currentGender !== "all") params.append("gender", currentGender);
      if (currentBudget && currentBudget !== "all") params.append("budget", currentBudget);
      if (currentSort && currentSort !== "none") params.append("sort", currentSort);

      const response = await fetch(`api/fetch_properties.php?${params.toString()}`);
      if (!response.ok) {
        throw new Error(`HTTP error ${response.status}`);
      }
      const data = await response.json();
      if (data.success) {
        setProperties(data.properties || []);
      } else {
        throw new Error(data.message || "Failed to load properties");
      }
    } catch (err) {
      setError(err.message || "Something went wrong while fetching properties.");
    } finally {
      setLoading(false);
      if (spinner) spinner.style.display = "none";
    }
  }, []);

  // Initial load
  useEffect(() => {
    fetchProperties(city, gender, budget, sort);
  }, [fetchProperties, city, gender, budget, sort]);

  // Open Filter Modal
  const openFilterModal = () => {
    setTempCity(city);
    setTempGender(gender);
    setTempBudget(budget);
    setIsModalOpen(true);
  };

  // Close Filter Modal
  const closeFilterModal = () => {
    setIsModalOpen(false);
  };

  // Apply Filters
  const applyFilters = () => {
    setCity(tempCity);
    setGender(tempGender);
    setBudget(tempBudget);
    setIsModalOpen(false);
  };

  // Reset Filters
  const resetFilters = () => {
    setTempGender("all");
    setTempBudget("all");
  };

  // Toggle Sorting
  const toggleSort = (type) => {
    setSort((prevSort) => (prevSort === type ? "none" : type));
  };

  // Toggle Interest via AJAX
  const toggleInterested = async (propertyId) => {
    if (togglingIds.has(propertyId)) return; // Debounce in-flight

    setTogglingIds((prev) => new Set(prev).add(propertyId));

    try {
      const response = await fetch(`api/toggle_interested.php?property_id=${encodeURIComponent(propertyId)}`);
      const data = await response.json();

      if (data.is_logged_in === false) {
        if (typeof window.$ !== "undefined") {
          window.$("#login-modal").modal("show");
        } else {
          alert("Please login to mark this property as interested!");
        }
        return;
      }

      if (data.success) {
        setProperties((prev) =>
          prev.map((prop) => {
            if (prop.id === propertyId) {
              const updatedInterest = data.is_interested;
              const countDiff = updatedInterest ? 1 : -1;
              return {
                ...prop,
                is_interested: updatedInterest,
                interested_count: Math.max(0, prop.interested_count + countDiff)
              };
            }
            return prop;
          })
        );
      } else if (data.message) {
        alert(data.message);
      }
    } catch (err) {
      alert("Network error: Could not update interest.");
    } finally {
      setTogglingIds((prev) => {
        const next = new Set(prev);
        next.delete(propertyId);
        return next;
      });
    }
  };

  const hasActiveFilters = gender !== "all" || budget !== "all";

  return (
    <div className="react-property-list-container">
      {/* Filter Bar */}
      <div className="filter-bar row justify-content-around">
        <div
          className={`col-auto ${hasActiveFilters ? "filter-active" : ""}`}
          onClick={openFilterModal}
          style={{ cursor: "pointer" }}
        >
          <img src="img/filter.png" alt="filter" />
          <span>Filter {hasActiveFilters ? " (Active)" : ""}</span>
        </div>
        <div
          className="col-auto"
          onClick={() => toggleSort("highest")}
          style={{ cursor: "pointer", opacity: sort === "highest" ? 1 : 0.85 }}
        >
          <img
            src="img/desc.png"
            alt="sort-desc"
            style={{
              borderColor: sort === "highest" ? "#EA322E" : "#646870",
              borderWidth: sort === "highest" ? "2px" : "1px"
            }}
          />
          <span style={{ fontWeight: sort === "highest" ? "bold" : "normal", color: sort === "highest" ? "#EA322E" : "inherit" }}>
            Highest rent first
          </span>
        </div>
        <div
          className="col-auto"
          onClick={() => toggleSort("lowest")}
          style={{ cursor: "pointer", opacity: sort === "lowest" ? 1 : 0.85 }}
        >
          <img
            src="img/asc.png"
            alt="sort-asc"
            style={{
              borderColor: sort === "lowest" ? "#EA322E" : "#646870",
              borderWidth: sort === "lowest" ? "2px" : "1px"
            }}
          />
          <span style={{ fontWeight: sort === "lowest" ? "bold" : "normal", color: sort === "lowest" ? "#EA322E" : "inherit" }}>
            Lowest rent first
          </span>
        </div>
      </div>

      {/* Error state */}
      {error && (
        <div className="alert alert-danger text-center my-4" role="alert">
          {error}{" "}
          <button className="btn btn-sm btn-outline-danger ml-2" onClick={() => fetchProperties(city, gender, budget, sort)}>
            Retry
          </button>
        </div>
      )}

      {/* Property Cards List */}
      {!loading && properties.length === 0 && !error ? (
        <div className="no-property-container">
          <p>No PG to list</p>
        </div>
      ) : (
        properties.map((property) => (
          <div key={property.id} className={`property-card property-id-${property.id} row`}>
            <div className="image-container col-md-4">
              <img src={property.image} alt={property.name} />
            </div>
            <div className="content-container col-md-8">
              <div className="row no-gutters justify-content-between">
                <StarRating rating={property.total_rating} />
                <div className="interested-container">
                  <i
                    className={`is-interested-image ${property.is_interested ? "fas" : "far"} fa-heart`}
                    property_id={property.id}
                    onClick={() => toggleInterested(property.id)}
                    style={{ cursor: "pointer" }}
                  ></i>
                  <div className="interested-text">
                    <span className="interested-user-count">{property.interested_count}</span> interested
                  </div>
                </div>
              </div>

              <div className="detail-container">
                <div className="property-name">{property.name}</div>
                <div className="property-address">{property.address}</div>
                <div className="property-gender">
                  {property.gender === "male" && <img src="img/male.png" alt="male" />}
                  {property.gender === "female" && <img src="img/female.png" alt="female" />}
                  {property.gender !== "male" && property.gender !== "female" && <img src="img/unisex.png" alt="unisex" />}
                </div>
              </div>

              <div className="row no-gutters">
                <div className="rent-container col-6">
                  <div className="rent">Rs {property.rent.toLocaleString()}/-</div>
                  <div className="rent-unit">per month</div>
                </div>
                <div className="button-container col-6">
                  <a href={`property_detail.php?property_id=${property.id}`} className="btn btn-primary">
                    View
                  </a>
                </div>
              </div>
            </div>
          </div>
        ))
      )}

      {/* Filter Modal */}
      {isModalOpen && (
        <div
          id="filter-modal"
          className="modal fade show"
          style={{ display: "block", backgroundColor: "rgba(0, 0, 0, 0.5)", zIndex: 1050 }}
          tabIndex="-1"
          role="dialog"
          onClick={closeFilterModal}
        >
          <div className="modal-dialog modal-dialog-centered" role="document" onClick={(e) => e.stopPropagation()}>
            <div className="modal-content">
              <div className="modal-header">
                <h3 className="modal-title" id="filter-heading">
                  Filters
                </h3>
                <button type="button" className="close" aria-label="Close" onClick={closeFilterModal}>
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>

              <div className="modal-body">
                {/* City Selection */}
                <h5>City</h5>
                <hr />
                <div className="mb-3">
                  {["All Cities", "Delhi", "Mumbai", "Bengaluru", "Hyderabad"].map((cityName) => {
                    const isSelected = (cityName === "All Cities" && !tempCity) || tempCity.toLowerCase() === cityName.toLowerCase();
                    return (
                      <button
                        key={cityName}
                        type="button"
                        className={`btn btn-outline-dark ${isSelected ? "btn-active" : ""}`}
                        style={{ margin: "6px", width: "auto", minWidth: "90px" }}
                        onClick={() => setTempCity(cityName === "All Cities" ? "" : cityName)}
                      >
                        {cityName}
                      </button>
                    );
                  })}
                </div>

                {/* Gender Filter */}
                <h5>Gender</h5>
                <hr />
                <div className="mb-3">
                  <button
                    type="button"
                    className={`btn btn-outline-dark ${tempGender === "all" ? "btn-active" : ""}`}
                    onClick={() => setTempGender("all")}
                  >
                    No Filter
                  </button>
                  <button
                    type="button"
                    className={`btn btn-outline-dark ${tempGender === "unisex" ? "btn-active" : ""}`}
                    onClick={() => setTempGender("unisex")}
                  >
                    <i className="fas fa-venus-mars"></i>Unisex
                  </button>
                  <button
                    type="button"
                    className={`btn btn-outline-dark ${tempGender === "male" ? "btn-active" : ""}`}
                    onClick={() => setTempGender("male")}
                  >
                    <i className="fas fa-mars"></i>Male
                  </button>
                  <button
                    type="button"
                    className={`btn btn-outline-dark ${tempGender === "female" ? "btn-active" : ""}`}
                    onClick={() => setTempGender("female")}
                  >
                    <i className="fas fa-venus"></i>Female
                  </button>
                </div>

                {/* Budget Filter */}
                <h5>Budget (Rent / Month)</h5>
                <hr />
                <div>
                  <button
                    type="button"
                    className={`btn btn-outline-dark ${tempBudget === "all" ? "btn-active" : ""}`}
                    style={{ width: "auto", minWidth: "100px" }}
                    onClick={() => setTempBudget("all")}
                  >
                    No Filter
                  </button>
                  <button
                    type="button"
                    className={`btn btn-outline-dark ${tempBudget === "max_6000" ? "btn-active" : ""}`}
                    style={{ width: "auto", minWidth: "120px" }}
                    onClick={() => setTempBudget("max_6000")}
                  >
                    Under ₹6,000
                  </button>
                  <button
                    type="button"
                    className={`btn btn-outline-dark ${tempBudget === "6000_8500" ? "btn-active" : ""}`}
                    style={{ width: "auto", minWidth: "140px" }}
                    onClick={() => setTempBudget("6000_8500")}
                  >
                    ₹6,000 - ₹8,500
                  </button>
                  <button
                    type="button"
                    className={`btn btn-outline-dark ${tempBudget === "above_8500" ? "btn-active" : ""}`}
                    style={{ width: "auto", minWidth: "120px" }}
                    onClick={() => setTempBudget("above_8500")}
                  >
                    Above ₹8,500
                  </button>
                </div>
              </div>

              <div className="modal-footer justify-content-between">
                <button type="button" className="btn btn-outline-secondary" onClick={resetFilters}>
                  Clear All
                </button>
                <button type="button" className="btn btn-success" onClick={applyFilters}>
                  Okay
                </button>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}

// Auto mount onto root element when DOM is loaded
function mountPropertyList() {
  const rootElement = document.getElementById("react-property-list-root");
  if (rootElement) {
    const initialCity = rootElement.getAttribute("data-city") || "";
    const root = createRoot(rootElement);
    root.render(<PropertyList initialCity={initialCity} />);
  }
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", mountPropertyList);
} else {
  mountPropertyList();
}

export default PropertyList;
