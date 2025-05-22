# ExternIT Design System Guidelines (Emerging)

This document outlines the emerging design guidelines for ExternIT, ensuring a cohesive and scalable design experience across all student- and company-facing pages. It reflects the most recent refinements across dashboards, forms, and interactive elements.

## 1. General Principles

*   **Consistency:** Maintain a unified aesthetic across dashboards, task pages, and forms.
*   **Modularity:** Structure CSS modularly (`_buttons.css`, `_cards.css`, etc.), reducing inline styling.
*   **Clarity:** Prioritize visual hierarchy and usability, especially in card layouts and interactive forms.

## 2. Colors

*   **Primary Theme (Student):**  
    - Teal/Green: `#056954` (`--student-primary`)  
    - Usage: Primary buttons, headers, icons, links, gradients.

*   **Secondary Theme (Company):**  
    - Burnt Orange: `#BF6D3A` (`--company-primary`)  
    - Usage: Company elements (buttons, cards, accents).

*   **Gradients:**  
    - Common: `linear-gradient(45deg, #056954, #033f32)`  
    - Used in card headers, banners.

*   **Text Colors:**  
    - Standard Dark: `--text-dark`  
    - Light: `--text-light` (for dark backgrounds)  
    - Muted: `#6C757D` (for meta/secondary info)

*   **Status Colors:**  
    - **Accepted/Open:** `#2D9CDB`, bg `rgba(45, 156, 219, 0.1)`  
    - **Pending:** `#F2994A`, bg `rgba(242, 153, 74, 0.1)`  
    - **Rejected:** `#EB5757`, bg `rgba(235, 87, 87, 0.1)`

*   **Backgrounds:**  
    - Default: `#FFFFFF`  
    - Soft backgrounds: `#E9ECEF` or `var(--border-color)`

## 3. Border Radius

| Element             | Radius |
|---------------------|--------|
| Buttons             | 6px    |
| Inputs & Selects    | 6px    |
| Badges/Tags         | 6px    |
| Cards               | 12px   |
| Profile Images      | 50%    |
| Progress Bars       | 4px    |

## 4. Components

### Buttons

*   Padding: `0.625rem 1.25rem` or `0.5rem 1.25rem`
*   Interaction:  
    - Hover: Slight color change  
    - Animation: `transform: translateY(-1px)`
*   Use themed classes: `.student-btn`, `.btn-outline-student`, `.company-btn`
*   With icons: `display: inline-flex`, `gap: 0.5rem`, align center

### Inputs & Selects

*   Padding: `0.75rem 1rem`
*   Border: `2px solid var(--border-color)`
*   Focus: Border and shadow reflect theme color

### Cards

*   Header: Gradient background, white text
*   Body Padding: `1.25rem` or `1.5rem`
*   Depth: `box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1)`
*   Hover: `transform: translateY(-2px)`, slightly stronger shadow

### Badges / Labels / Meta Items

*   Style: Tags (not buttons)
*   Background: `rgba(..., 0.1)`
*   Border: `1px solid rgba(..., 0.2)`
*   Padding: `0.375rem 0.875rem`
*   With icons:  
    - Font size: `0.85rem` – `0.9rem`  
    - Layout: `display: inline-flex`, `gap`, `align-items: center`

## 5. Spacing & Layout

*   **Flexbox:** For layout of cards, headers, filter panels, badge lists
*   **Gap:** Use `gap` instead of `margin-right/left` in flex (`0.5rem`, `1rem`)
*   **Margins:**  
    - Bootstrap utilities: `mb-3`, `mb-4`  
    - Custom: `margin: 1rem 0` (e.g., below progress bars)
*   **Padding:** Consistent padding across buttons, inputs, and card sections

## 6. Typography

| Element             | Weight | Size    |
|---------------------|--------|---------|
| Card Headers        | 600    | 1.2rem  |
| Buttons / Meta Text | 500    | 0.95rem |
| Badges / Tags       | 500    | 0.85rem |

*   Font should emphasize hierarchy and readability.
*   Avoid overly light weights below 500.

## 7. Icons

*   **Source:** Bootstrap Icons (`bi-*`)
*   **Use Cases:**  
    - Buttons  
    - Meta items (task details, time, deadline)  
    - Badges
*   **Styling:**  
    - Font size: `0.85rem – 1rem`  
    - Layout: Inline-flex with text, use gap 