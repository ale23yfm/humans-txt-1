window.onload = function () {
  document
    .getElementById("urlInput")
    .addEventListener("keyup", function (event) {
      event.preventDefault();
      if (event.keyCode === 13) {
        // 13 is Entry key code
        document.getElementById("checkButton").click();
      }
    });
};

async function fetchData(domain) {
  const apiUrl = `https://humans-txt.42web.io/?domain=${domain}`;

  try {
    const response = await fetch(apiUrl);

    // Always log status for debugging
    console.log("HTTP status:", response.status);

    if (!response.ok) {
      const errText = await response.text();
      console.error("Server error:", errText);
      return false;
    }

    // Try to detect JSON vs text
    const contentType = response.headers.get("content-type");
    if (contentType && contentType.includes("application/json")) {
      return await response.json();
    } else {
      return await response.text();
    }
  } catch (error) {
    console.error("Fetch error:", error);
    return false;
  }
}

async function extractDomain() {
  // Get the input value
  const url = document.getElementById("urlInput").value.trim();

  // Regular expression to match and extract the domain
  const domainRegex = /^(https?:\/\/)?(www\.)?([a-zA-Z0-9.-]+\.[a-zA-Z]{2,6})/;
  const matches = url.match(domainRegex);

  const domainElement = document.getElementById("domain");
  const humansElement = document.getElementById("humans");

  if (matches && matches[3]) {
    // Extracted domain name
    const domain = matches[3];
    const humansTxt = await fetchData(domain);
    // Pass the domain to the engine or display it
    domainElement.textContent = "Extracted Domain: " + domain;
    humansElement.textContent = humansTxt ? humansTxt : `humans.txt not found!`;
    humansElement.style.color = humansTxt ? "#c6c6c6" : "red";
    humansElement.style.display = "block";
    // You can also redirect to the engine URL with the extracted domain
    // window.location.href = "https://your-engine-url/?domain=" + domain;
  } else {
    domainElement.textContent = "Invalid URL";
    humansElement.style.display = "none";
  }

  showResultContainer();
}

function showResultContainer() {
  const resultContainerElement = document.querySelector(".result-container");

  if (resultContainerElement) {
    resultContainerElement.style.display = "block";
  } else {
    console.warn("No element with the class 'result-container' found.");
  }
}
