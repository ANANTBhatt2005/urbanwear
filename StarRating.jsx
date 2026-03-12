import React, { useState } from 'react';
import './StarRating.css';

const StarRating = ({ totalStars = 5, initialRating = 0, onRate }) => {
  const [rating, setRating] = useState(initialRating);
  const [hover, setHover] = useState(0);

  // Calculate rating based on mouse position within the star
  const handleMouseMove = (e, index) => {
    const rect = e.currentTarget.getBoundingClientRect();
    const width = rect.width;
    const x = e.clientX - rect.left;
    
    // If cursor is on the left half, it's a half star (x.5)
    // Otherwise it's a full star (x.0)
    const isHalf = x < width / 2;
    const value = isHalf ? index + 0.5 : index + 1;
    
    setHover(value);
  };

  const handleClick = () => {
    setRating(hover);
    if (onRate) {
      onRate(hover);
    }
  };

  const handleMouseLeave = () => {
    setHover(0);
  };

  const renderStar = (index) => {
    // Use hover value if present, otherwise use saved rating
    const value = hover || rating;
    
    // Determine visual state
    const isFull = value >= index + 1;
    const isHalf = value >= index + 0.5 && value < index + 1;

    return (
      <span 
        key={index}
        className="star-wrapper"
        onMouseMove={(e) => handleMouseMove(e, index)}
        onClick={handleClick}
      >
        {/* Base Empty Star */}
        <StarIcon className="star-empty" />
        
        {/* Overlays for Full or Half fill */}
        {isFull && <div className="star-overlay full"><StarIcon className="star-filled" /></div>}
        {isHalf && <div className="star-overlay half"><StarIcon className="star-filled" /></div>}
      </span>
    );
  };

  return (
    <div className="star-rating-container" onMouseLeave={handleMouseLeave}>
      <div className="stars">
        {[...Array(totalStars)].map((_, i) => renderStar(i))}
      </div>
      <div className="rating-label">{hover || rating || 0}</div>
    </div>
  );
};

// Simple SVG Star Component
const StarIcon = ({ className }) => (
  <svg 
    viewBox="0 0 24 24" 
    className={`star-svg ${className}`}
    xmlns="http://www.w3.org/2000/svg"
  >
    <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z" />
  </svg>
);

export default StarRating;