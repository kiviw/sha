var SimpleMatrix = (function() {
  // Private helper functions
  function multiplyMatrix(a, b) {
    var result = [];
    var aRows = a.length;
    var aCols = a[0].length;
    var bCols = b[0].length;

    for (var i = 0; i < aRows; i++) {
      result[i] = [];
      for (var j = 0; j < bCols; j++) {
        var sum = 0;
        for (var k = 0; k < aCols; k++) {
          sum += a[i][k] * b[k][j];
        }
        result[i][j] = sum;
      }
    }

    return result;
  }

  // Public API
  function SimpleMatrix(matrix) {
    this.matrix = matrix;
  }

  SimpleMatrix.prototype.multiply = function(otherMatrix) {
    if (!(otherMatrix instanceof SimpleMatrix)) {
      throw new Error('Invalid matrix');
    }

    var result = multiplyMatrix(this.matrix, otherMatrix.matrix);
    return new SimpleMatrix(result);
  };

  // Other methods...

  return SimpleMatrix;
})();
