const API = {
  baseURL: "../api/",

  async request(endpoint, method = "GET", body = null) {
    const url = this.baseURL + endpoint;
    const options = {
      method: method,
      headers: { "Content-Type": "application/json" },
    };

    if (body) {
      //converts javascript object or value into json string.
      options.body = JSON.stringify(body);
    }

    try {
      const response = await fetch(url, options);
      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.error || "Request failed");
      }

      return data;
    } catch (error) {
      showToast(error.message, "error");
      throw error;
    }
  },

  get(endpoint) {
    return this.request(endpoint, "GET");
  },

  post(endpoint, body) {
    return this.request(endpoint, "POST", body);
  },
};

function showToast(message, type = "success") {
  const toast = document.getElementById("toast");
  if (!toast) return;

  toast.textContent = message;
  toast.className = "toast " + type;
  toast.style.display = "block";

  setTimeout(() => {
    toast.style.display = "none";
  }, 3000);
}

function showPage(pageName) {
  document.querySelectorAll(".page").forEach((page) => {
    page.style.display = "none";
  });

  const targetPage = document.getElementById("page-" + pageName);
  if (targetPage) {
    targetPage.style.display = "block";
  }

  document.querySelectorAll(".sidebar-menu a").forEach((link) => {
    link.classList.remove("active");
  });

  const activeLink = document.querySelector(
    `[onclick="showPage('${pageName}')"]`
  );
  if (activeLink) {
    activeLink.classList.add("active");
  }
}

function openModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.classList.add("active");
  }
}

function closeModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.classList.remove("active");
  }
}

function logout() {
  fetch("../api/logout.php", { method: "POST" })
    .then(() => {
      window.location.href = "./login.html";
    })
    .catch(() => {
      window.location.href = "./login.html";
    });
}

function formatDate(dateStr) {
  const date = new Date(dateStr);
  return date.toLocaleDateString("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric",
  });
}

function formatTime(timeStr) {
  const [hours, minutes] = timeStr.split(":");
  const hour = parseInt(hours);
  const ampm = hour >= 12 ? "PM" : "AM";
  const displayHour = hour % 12 || 12;
  return `${displayHour}:${minutes} ${ampm}`;
}

async function checkAuth() {
  try {
    const result = await API.get("me.php");
    return result.data;
  } catch (error) {
    window.location.href = "./login.html";
    return null;
  }
}

function showLoading(elementId) {
  const element = document.getElementById(elementId);
  if (element) {
    element.innerHTML =
      '<div class="loading"><div class="spinner"></div></div>';
  }
}
