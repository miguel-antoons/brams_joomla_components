// Adapted from Creating an HTML5 canvas painting application
// Link: http://dev.opera.com/articles/view/html5-canvas-painting/
// Author: Mihai Sucan

addListener(window, 'load', initializeMeteorCounting);

function initializeMeteorCounting() {
    const FONT_SIZE = 12;
    let canvas; let context; let counting; let
        countingContext;

    // Get canvas element.
    counting = document.getElementById('mc_counting');
    if (!counting || !counting.getContext) {
        alert('Error: no mc_counting element!');
        return;
    }

    // Get the 2D canvas context.
    countingContext = counting.getContext('2d');
    if (!countingContext) {
        alert('Error: no counting context!');
        return;
    }

    // Get drawing canvas.
    canvas = document.getElementById('mc_canvas');
    if (!canvas || !canvas.getContext) {
        alert('Error: no mc_canvas element!');
        return;
    }

    context = canvas.getContext('2d');

    // Set style on canvas.
    context.font = countingContext.font = `bold ${FONT_SIZE}px sans-serif`;
    context.fillStyle = countingContext.fillStyle = 'red';
    context.strokeStyle = countingContext.strokeStyle = 'red';
    context.lineWidth = countingContext.lineWidth = 2;

    // Attach the mousedown, mousemove and mouseup event listeners to the drawing canvas.
    addListener(canvas, 'mousedown', drawRectangle);
    addListener(canvas, 'mousemove', drawRectangle);
    addListener(canvas, 'mouseup', drawRectangle);
    addListener(canvas, 'mouseout', drawRectangle);
    addListener(canvas, 'dblclick', removeRectangle);

    // Attach the keypress even listener to the document.
    addListener(document, 'keypress', (ev) => {
        let type; let
            c = 0;

        if (ev.charCode) {
            c = ev.charCode;
        } else if (ev.keyCode) {
            c = ev.keyCode;
        }

        if (c == 49 || c == 115 || c == 83) {
            type = 'S';
        } else if (c == 50 || c == 108 || c == 76) {
            type = 'L';
        } else {
            return;
        }

        selectMeteorType(type);
    }, false);

    // Get background canvas.
    background = document.getElementById('mc_background');
    if (!background || !background.getContext) {
        alert('Error: no mc_background element!');
        return;
    }

    // Add background image.
    const backgroundContext = background.getContext('2d');
    if (!backgroundContext || !backgroundContext.drawImage) {
        alert('Error: no content.drawImage!');
        return;
    }

    if (typeof mc_image_src !== 'undefined') {
        const img = new Image();
        addListener(img, 'load', () => {
            backgroundContext.drawImage(img, 0, 0);
        });
        img.src = mc_image_src;
    }

    // Draw initial meteors.
    for (let i = 0; i < mc_meteors.length; ++i) {
        mc_meteors[i].draw(counting);
    }

    // Select initial meteor type.
    selectMeteorType(mc_meteor_type);

    // Create the rectangle drawing tool.
    const tool = new RectangleTool();

    function drawRectangle(ev) {
        if (ev.layerX || ev.layerX == 0) { // Firefox
            ev._x = ev.layerX;
            ev._y = ev.layerY;
        } else if (ev.offsetX || ev.offsetX == 0) { // Opera
            ev._x = ev.offsetX;
            ev._y = ev.offsetY;
        }

        // Call the corresponding event handler of the rectangle tool.
        const func = tool[ev.type];
        if (func) {
            func(ev);
        }
    }

    function RectangleTool() {
        const tool = this;
        this.started = false;

        this.mousedown = function (ev) {
            // Prevent cursor to change to text-selection on Chrome.
            if (ev.preventDefault) {
                ev.preventDefault();
            }
            tool.started = true;
            tool.x0 = ev._x;
            tool.y0 = ev._y;
        };

        this.mousemove = function (ev) {
            if (!tool.started) {
                return;
            }

            const x = Math.min(ev._x, tool.x0);
            const y = Math.min(ev._y, tool.y0);
            const w = Math.abs(ev._x - tool.x0);
            const h = Math.abs(ev._y - tool.y0);

            context.clearRect(0, 0, canvas.width, canvas.height);

            if (!w || !h) {
                return;
            }

            const m = new Meteor(x, y, x + w, y + h, mc_meteor_type);
            m.draw(canvas);
        };

        this.mouseup = function (ev) {
            if (tool.started) {
                tool.mousemove(ev);
                tool.started = false;

                // Clear drawing canvas #mc_canvas.
                context.clearRect(0, 0, canvas.width, canvas.height);

                if (tool.x0 != ev._x && tool.y0 != ev._y) {
                    m = new Meteor(tool.x0, tool.y0, ev._x, ev._y, mc_meteor_type);
                    // Draws the meteor on the #mc_counting canvas.
                    m.draw(counting);
                    mc_meteors.push(m);
                }
            }
        };

        this.mouseout = function (ev) {
            if (tool.started) {
                // tool.mousemove(ev);
                tool.started = false;

                // Clear drawing canvas #mc_canvas.
                context.clearRect(0, 0, canvas.width, canvas.height);

                if (tool.x0 != ev._x && tool.y0 != ev._y) {
                    alert(`X0: ${tool.x0} Y0: ${tool.y0} X: ${ev._x} Y: ${ev._y}`);
                    alert(ev.relatedTarget.id);
                    // Keep rectangle inside canvas.
                    ev._x -= 52;
                    if (ev._x < 0) { ev._x = 0; }
                    if (ev._x >= canvas.width) { ev._x = canvas.width - 1; }

                    if (ev._y >= canvas.height) { ev._y = canvas.height - 1; }

                    m = new Meteor(tool.x0, tool.y0, ev._x, ev._y, mc_meteor_type);
                    // Draws the meteor on the #mc_counting canvas.
                    m.draw(counting);
                    mc_meteors.push(m);
                }
            }
        };
    }

    function removeRectangle(ev) {
        let x; let
            y;
        if (ev.layerX || ev.layerX == 0) { // Firefox
            x = ev.layerX;
            y = ev.layerY;
        } else if (ev.offsetX || ev.offsetX == 0) { // Opera
            x = ev.offsetX;
            y = ev.offsetY;
        }

        // Find the innermost rectangle in which the user clicked.
        // Note that the algorithm here is too naive for finding the most
        // expected rectangle each time, but it is far good enough for the
        // counting.
        let index = -1;
        let distance = Number.POSITIVE_INFINITY;
        for (var i = 0; i < mc_meteors.length; ++i) {
            if (mc_meteors[i].inside(x, y)) {
                const d = mc_meteors[i].distance(x, y);
                if (d < distance) {
                    distance = d;
                    index = i;
                }
            }
        }

        if (index != -1) {
            // Remove the nearest rectangle from the results.
            mc_meteors.splice(index, 1);

            // Remove the nearest rectangle from the counting canvas.
            countingContext.clearRect(0, 0, counting.width, counting.height);
            for (var i = 0; i < mc_meteors.length; ++i) {
                mc_meteors[i].draw(counting);
            }
        }

        // The user clicked outside any rectangles.
    }
}

function submitMeteors(form) {
    if (!form) {
        alert('Error: no form element!');
        return false;
    }

    let zoom = detectZoom.zoom();
    if (zoom && zoom != 1) {
        zoom = Math.round(100 * zoom);
        alert(`The current zoom level of your browser is ${zoom}%. Please reset your zoom to 100%.`);
        return false;
    }

    const prefix = 'data[ManualCountingMeteor]';

    const meteorInput = function (name, value) {
        const input = document.createElement('input');
        input.setAttribute('type', 'hidden');
        input.setAttribute('name', `${prefix}[${i}][${name}]`);
        input.setAttribute('value', value);
        return input;
    };

    for (var i = 0; i < mc_meteors.length; ++i) {
        form.appendChild(meteorInput('top', mc_meteors[i].top));
        form.appendChild(meteorInput('left', mc_meteors[i].left));
        form.appendChild(meteorInput('bottom', mc_meteors[i].bottom));
        form.appendChild(meteorInput('right', mc_meteors[i].right));
        form.appendChild(meteorInput('type', mc_meteors[i].type));
    }

    if (mc_meteor_type) {
        form.action += `/${mc_meteor_type}`;
    }
    return true;
}

function selectMeteorType(type) {
    const types = { S: 'short', L: 'long' };

    mc_meteor_type = '';
    for (const i in types) {
        // For zoo campaigns, the short and long icons do not exist.
        const img = document.getElementById(`mc_${types[i]}`);
        if (img) {
            if (i == type && img.src.replace(/^.*[\/]/, '') == `${types[i]}_icon.png`) {
                img.src = `/img/${types[i]}_selected_icon.png`;
                mc_meteor_type = i;
            } else {
                img.src = `/img/${types[i]}_icon.png`;
            }
        }
    }
}

// This function creates a new Meteor object.
function Meteor(x0, y0, x1, y1, type) {
    const meteor = this;

    if (x0 <= x1) {
        meteor.left = x0;
        meteor.right = x1;
    } else {
        meteor.left = x1;
        meteor.right = x0;
    }

    if (y0 <= y1) {
        meteor.top = y0;
        meteor.bottom = y1;
    } else {
        meteor.top = y1;
        meteor.bottom = y0;
    }

    if (typeof (type) === 'undefined') {
        meteor.type = '';
    } else {
        meteor.type = type;
    }

    // This function returns true if the (x,y) point is inside the meteor.
    this.inside = function (x, y) {
        return meteor.left <= x && x <= meteor.right && meteor.top <= y && y <= meteor.bottom;
    };

    // This function computes a qualitative distance from the center of the meteor.
    this.distance = function (x, y) {
        const cx = meteor.left + (meteor.right - meteor.left) / 2;
        const cy = meteor.top + (meteor.bottom - meteor.top) / 2;
        return (cx - x) * (cx - x) + (cy - y) * (cy - y);
    };

    // Draw a meteor on the given canvas.
    this.draw = function (canvas) {
        context = canvas.getContext('2d');
        if (!context) {
            alert('Error: no canvascontext!');
            return;
        }

        const width = meteor.right - meteor.left;
        const height = meteor.bottom - meteor.top;
        context.strokeRect(meteor.left, meteor.top, width, height);

        const fontSize = parseInt(context.font.slice(5, 7)); // TODO: improve
        let x = meteor.right;
        let y = meteor.top;

        if (x + fontSize >= canvas.width) {
            x = meteor.left - fontSize;
        }

        if (y - fontSize <= 0) {
            y = meteor.bottom + fontSize;
        }

        context.fillText(meteor.type, x, y);
    };
}

function createCanvas() {
    canvas = document.createElement('canvas');
    if (!canvas) {
        alert('Error: creating a new canvas element failed!');
        return false;
    }

    if (!canvas.getContext && G_vmlCanvasManager) {
        canvas = G_vmlCanvasManager.initElement(canvas);
    }

    if (!canvas.getContext) {
        alert('Error: newly created canvas has no context!');
        return false;
    }

    return canvas;
}

function addListener(el, ev, listener) {
    if (el.addEventListener) {
        el.addEventListener(ev, listener, false);
    } else if (el.attachEvent) {
        el.attachEvent(`on${ev}`, listener);
    } else if (el[`on${ev.charAt(0).toUpperCase()}`]) {
        el[`on${ev.charAt(0).toUpperCase()}`] = listener;
    }
}
