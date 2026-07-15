const canvas = document.querySelector('#board');
const context = canvas.getContext('2d');
const toolInput = document.querySelector('#tool');
const colorInput = document.querySelector('#color');
const sizeInput = document.querySelector('#size');
const sizeLabel = document.querySelector('#sizeLabel');
const textInput = document.querySelector('#textValue');
const undoButton = document.querySelector('#undo');
const clearButton = document.querySelector('#clear');
const exportButton = document.querySelector('#exportPng');

const boardState = {
  operations: [],
  activeStroke: null
};

function pointFromEvent(event) {
  const rect = canvas.getBoundingClientRect();
  return {
    x: Math.round((event.clientX - rect.left) * (canvas.width / rect.width)),
    y: Math.round((event.clientY - rect.top) * (canvas.height / rect.height))
  };
}

function currentStyle() {
  return {
    color: colorInput.value,
    size: Number(sizeInput.value)
  };
}

function redraw() {
  context.clearRect(0, 0, canvas.width, canvas.height);
  context.fillStyle = '#ffffff';
  context.fillRect(0, 0, canvas.width, canvas.height);
  for (const operation of boardState.operations) {
    drawOperation(operation);
  }
  if (boardState.activeStroke) {
    drawOperation(boardState.activeStroke);
  }
}

function drawOperation(operation) {
  if (operation.type === 'stroke') {
    drawStroke(operation);
    return;
  }
  if (operation.type === 'text') {
    drawText(operation);
  }
}

function drawStroke(stroke) {
  if (stroke.points.length < 2) {
    return;
  }
  context.save();
  context.lineCap = 'round';
  context.lineJoin = 'round';
  context.lineWidth = stroke.size;
  context.strokeStyle = stroke.tool === 'eraser' ? '#ffffff' : stroke.color;
  context.beginPath();
  context.moveTo(stroke.points[0].x, stroke.points[0].y);
  for (const point of stroke.points.slice(1)) {
    context.lineTo(point.x, point.y);
  }
  context.stroke();
  context.restore();
}

function drawText(operation) {
  context.save();
  context.fillStyle = operation.color;
  context.font = `${operation.size * 4}px system-ui, sans-serif`;
  context.textBaseline = 'top';
  context.fillText(operation.text, operation.x, operation.y);
  context.restore();
}

function beginStroke(event) {
  const tool = toolInput.value;
  const point = pointFromEvent(event);
  if (tool === 'text') {
    const text = textInput.value.trim();
    if (text !== '') {
      const style = currentStyle();
      boardState.operations.push({
        type: 'text',
        text,
        x: point.x,
        y: point.y,
        color: style.color,
        size: style.size
      });
      redraw();
    }
    return;
  }

  const style = currentStyle();
  boardState.activeStroke = {
    type: 'stroke',
    tool,
    color: style.color,
    size: style.size,
    points: [point]
  };
  canvas.setPointerCapture(event.pointerId);
}

function continueStroke(event) {
  if (!boardState.activeStroke) {
    return;
  }
  boardState.activeStroke.points.push(pointFromEvent(event));
  redraw();
}

function endStroke(event) {
  if (!boardState.activeStroke) {
    return;
  }
  boardState.activeStroke.points.push(pointFromEvent(event));
  boardState.operations.push(boardState.activeStroke);
  boardState.activeStroke = null;
  redraw();
}

function exportPng() {
  const link = document.createElement('a');
  link.download = 'sample41-whiteboard.png';
  link.href = canvas.toDataURL('image/png');
  link.click();
}

sizeInput.addEventListener('input', () => {
  sizeLabel.textContent = sizeInput.value;
});

canvas.addEventListener('pointerdown', beginStroke);
canvas.addEventListener('pointermove', continueStroke);
canvas.addEventListener('pointerup', endStroke);
canvas.addEventListener('pointerleave', endStroke);
canvas.addEventListener('pointercancel', endStroke);

undoButton.addEventListener('click', () => {
  boardState.operations.pop();
  redraw();
});

clearButton.addEventListener('click', () => {
  boardState.operations = [];
  boardState.activeStroke = null;
  redraw();
});

exportButton.addEventListener('click', exportPng);

redraw();

export { boardState, redraw };
