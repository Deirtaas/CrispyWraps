const CUSTOMER_NAV = [
  ["index.php", "Home"],
  ["menu.php", "Menu"],
  ["cart.php", "Cart"],
  ["my-orders.php", "My Orders"],
  ["feedback.php", "Feedback"],
];

const STAFF_NAV = [
  ["dashboard.php", "Dashboard", "DB"],
  ["orders.php", "Order Operations", "OR"],
  ["inventory.php", "Inventory", "IN"],
  ["recipes.php", "Recipe Mapping", "RM"],
  ["menu.php", "Menu Configuration", "MC"],
  ["analytics.php", "Analytics", "AN"],
  ["reviews.php", "Customer Reviews", "CR"],
];

document.addEventListener("DOMContentLoaded", () => {
  const staffLogout = document.querySelector("#staff-logout");
  if (staffLogout) {
    staffLogout.addEventListener("click", (e) => {
      e.preventDefault();
      location.href = "../logout.php";
    });
  }

  const logoutBtn = document.querySelector("#logout-btn");
  if (logoutBtn) {
    logoutBtn.addEventListener("click", (e) => {
      e.preventDefault();
      location.href = "logout.php";
    });
  }
});