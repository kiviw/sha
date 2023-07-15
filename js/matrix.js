const matrixCodeElement = document.querySelector(".matrix-code");

// Modify the matrix code content and animation as desired
// For example, you can use a JavaScript library like "simple-matrix" to generate the Matrix code effect

// Example using simple-matrix library
const matrixCode = new SimpleMatrix(matrixCodeElement);

// Add the link within the Matrix code
matrixCode.setCallback(() => {
  // Customize the interval when the link appears within the Matrix code
  if (matrixCode.getTickCount() >= 100 && matrixCode.getTickCount() <= 150) {
    const linkElement = document.createElement("a");
    linkElement.href = "https://shadowcipher.ru/level4";
    linkElement.textContent = "Click here to proceed";

    // Customize the appearance of the link within the Matrix code
    linkElement.style.color = "#0f0"; // Green color
    linkElement.style.textDecoration = "none"; // Remove underline
    linkElement.style.fontWeight = "bold"; // Make the link bold

    matrixCodeElement.appendChild(linkElement);
  }
});

matrixCode.start();
