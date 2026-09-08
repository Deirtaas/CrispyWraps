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

// Inside your mountStaffShell function, update the logout click event:
$("#staff-logout").onclick = () => {
  location.href = "../logout.php";
};

// Inside your mountCustomerNav function, update the logout click event:
const lo = $("#logout-btn");
if (lo) {
  lo.onclick = () => {
    location.href = "logout.php";
  };
}