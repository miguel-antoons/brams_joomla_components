/* eslint-disable no-global-assign */
// * cf. ../../_js/edit.js
// eslint-disable-next-line no-unused-vars
/* global $, elementId, codes, log, apiFailMessg, newElement, updateElement, getCodes, detectZoom */
const currentView = 'countingEdit';
let spectrograms;
let meteors = [];
let currentIndex = 0;
let canvasClass;


function spectrogramChange() {
    const canvas = document.getElementById('mc_canvas');
    canvas.removeEventListener('mousedown', canvasClass.drawRectangle);
    canvas.removeEventListener('mousemove', canvasClass.drawRectangle);
    canvas.removeEventListener('mouseup', canvasClass.drawRectangle);
    canvas.removeEventListener('dblclick', canvasClass.removeRectangle);

    canvasClass = new MeteorCounting();
    // Attach the mousedown, mousemove and mouseup event listeners to the drawing canvas.
    addListener(canvas, 'mousedown', canvasClass.drawRectangle);
    addListener(canvas, 'mousemove', canvasClass.drawRectangle);
    addListener(canvas, 'mouseup', canvasClass.drawRectangle);
    addListener(canvas, 'dblclick', canvasClass.removeRectangle);
}


function goTo(subtract = false, index = undefined) {
    if (index !== undefined) {
        currentIndex = Number(index);
    } else if (subtract && currentIndex > 0) {
        currentIndex -= 1;
    } else if (currentIndex < (spectrograms.length - 1) && !subtract) {
        currentIndex += 1;
    } else return;

    setCanvasDim();
    meteors = [];
    setMeteors();
    spectrogramChange();
    document.getElementById('spectrogramNames').value = currentIndex;
}


function setSpectrogramOptions() {
    let HTMLString = '';

    spectrograms.forEach(
        (spectrogram, index) => {
            HTMLString += `
                <option value='${index}'>
                    ${spectrogram['start']}
                </option>
            `;
        }
    );

    document.getElementById('spectrogramNames').innerHTML = HTMLString;
}


function newMeteor(meteor, counting) {
    let zoom = detectZoom.zoom();
    if (zoom && zoom !== 1) {
        zoom = Math.round(100 * zoom);
        alert(`The current zoom level of your browser is ${zoom}%. Please reset your zoom to 100%.`);
        return false;
    }

    const token = $('#token').attr('name');
    $.ajax({
        type: 'POST',
        url: `
            /index.php?
            option=com_bramscampaign
            &task=addMeteor
            &model=spectrogram
            &view=${currentView}
            &format=json
            &${token}=1
        `,
        data: {
            newMeteor: {
                'left': meteor.left,
                'top': meteor.top,
                'right': meteor.right,
                'bottom': meteor.bottom,
                'type': meteor.type,
                'mcId': elementId,
                'spectrogramId': spectrograms[currentIndex]['id'],
            },
        },
        success: (response) => {
            meteor.id = response.data.newId;
            // Draws the meteor on the #mc_counting canvas, after which
            // #mc_canvas is cleared.
            meteor.draw(counting);
            meteors.push(meteor);
        },
        error: (response) => {
            // on fail, show an error message
            document.getElementById('error').innerHTML = (
                'API call failed, please read the \'log\' variable in '
                + 'developer console for more information about the problem.'
            );
            // store the server response in the log variable
            log = response;
        },
    });
}


function deleteMeteor(meteorIndex, counting, countingContext) {
    const token = $('#token').attr('name');

    $.ajax({
        type: 'DELETE',
        url: `
            /index.php?
            option=com_bramscampaign
            &task=deleteMeteor
            &model=spectrogram
            &view=${currentView}
            &format=json
            &id=${meteors[meteorIndex].id}
            &${token}=1
        `,
        success: (response) => {
            if (response.data.success) {
                // Remove the nearest rectangle from the results.
                meteors.splice(meteorIndex, 1);

                // Remove the nearest rectangle from the counting canvas.
                countingContext.clearRect(0, 0, counting.width, counting.height);
                for (let i = 0; i < meteors.length; ++i) {
                    meteors[i].draw(counting);
                }
            }
        },
        error: (response) => {
            // on fail, show an error message
            document.getElementById('error').innerHTML = (
                'API call failed, please read the \'log\' variable in '
                + 'developer console for more information about the problem.'
            );
            // store the server response in the log variable
            log = response;
        },
    });
}


function setCanvasDim() {
    const mcCounting = document.getElementById('mc_counting');
    mcCounting.width = spectrograms[currentIndex]['width'];
    mcCounting.height = spectrograms[currentIndex]['height'];

    const mcBackground = document.getElementById('mc_background');
    mcBackground.width = spectrograms[currentIndex]['width'];
    mcBackground.height = spectrograms[currentIndex]['height'];

    const mcCanvas = document.getElementById('mc_canvas');
    mcCanvas.width = spectrograms[currentIndex]['width'];
    mcCanvas.height = spectrograms[currentIndex]['height'];
}


function setMeteors() {
    spectrograms[currentIndex]['meteors'].forEach(
        (meteor) => {
            meteors.push(
                new Meteor(
                    meteor['left'],
                    meteor['top'],
                    meteor['right'],
                    meteor['bottom'],
                    meteor['type'],
                    meteor['id']
                )
            );
        }
    );
}


function setupPage() {
    // Get drawing canvas.
    const canvas = document.getElementById('mc_canvas');
    canvasClass = new MeteorCounting();

    // Attach the mousedown, mousemove and mouseup event listeners to the drawing canvas.
    addListener(canvas, 'mousedown', canvasClass.drawRectangle);
    addListener(canvas, 'mousemove', canvasClass.drawRectangle);
    addListener(canvas, 'mouseup', canvasClass.drawRectangle);
    addListener(canvas, 'dblclick', canvasClass.removeRectangle);
}


function getSpectrograms() {
    const token = $('#token').attr('name');

    $.ajax({
        type: 'GET',
        url: `
            /index.php?
            option=com_bramscampaign
            &task=getSpectrograms
            &model=spectrogram,campaigns
            &view=${currentView}
            &format=json
            &id=${elementId}
            &${token}=1
        `,
        success: (response) => {
            spectrograms = response.data;
            setCanvasDim();
            setMeteors();
            setupPage();
            setSpectrogramOptions();
        },
        error: (response) => {
            // on fail, show an error message
            document.getElementById('error').innerHTML = (
                'API call failed, please read the \'log\' variable in '
                + 'developer console for more information about the problem.'
            );
            // store the server response in the log variable
            log = response;
        },
    });
}

function onLoad() {
    // get the id from url
    const paramString = window.location.search.split('?')[1];
    const queryString = new URLSearchParams(paramString);
    elementId = Number(queryString.get('id'));

    if (elementId) {
        getSpectrograms();
    }
}

window.onload = onLoad;


// ! OLD CODE
// Adapted from Creating an HTML5 canvas painting application
// Link: http://dev.opera.com/articles/view/html5-canvas-painting/
// Author: Mihai Sucan


function MeteorCounting() {
    const FONT_SIZE = 12;
    const canvas = document.getElementById('mc_canvas');
    const context = canvas.getContext('2d');
    const counting = document.getElementById('mc_counting');
    // Get the 2D canvas context.
    const countingContext = counting.getContext('2d');

    // Get background canvas.
    let background = document.getElementById('mc_background');
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

    // Set style on canvas.
    context.font = countingContext.font = `bold ${FONT_SIZE}px sans-serif`;
    context.fillStyle = countingContext.fillStyle = 'red';
    context.strokeStyle = countingContext.strokeStyle = 'red';
    context.lineWidth = countingContext.lineWidth = 2;

    if (typeof spectrograms !== 'undefined') {
        const img = new Image();
        addListener(img, 'load', () => {
            backgroundContext.drawImage(img, 0, 0);
        });
        img.src = `/ProjectDir${spectrograms[currentIndex]['url']}`;
    }

    // Draw initial meteors.
    for (let i = 0; i < meteors.length; ++i) {
        meteors[i].draw(counting);
    }

    // Create the rectangle drawing tool.
    const tool = new RectangleTool();

    this.drawRectangle = (ev) => {
        if (ev.layerX || ev.layerX === 0) { // Firefox
            ev._x = ev.layerX;
            ev._y = ev.layerY;
        } else if (ev.offsetX || ev.offsetX === 0) { // Opera
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

            const m = new Meteor(x, y, x + w, y + h, '');
            m.draw(canvas);
        };

        this.mouseup = function (ev) {
            if (tool.started) {
                tool.mousemove(ev);
                tool.started = false;

                context.clearRect(0, 0, canvas.width, canvas.height);

                if (tool.x0 !== ev._x && tool.y0 !== ev._y) {
                    let m = new Meteor(tool.x0, tool.y0, ev._x, ev._y, '', 0);
                    newMeteor(m, counting);
                }
            }
        };
    }

    this.removeRectangle = (ev) => {
        let x; let
            y;
        if (ev.layerX || ev.layerX === 0) { // Firefox
            x = ev.layerX;
            y = ev.layerY;
        } else if (ev.offsetX || ev.offsetX === 0) { // Opera
            x = ev.offsetX;
            y = ev.offsetY;
        }

        // Find the innermost rectangle in which the user clicked.
        // Note that the algorithm here is too naive for finding the most
        // expected rectangle each time, but it is far good enough for the
        // counting.
        let index = -1;
        let distance = Number.POSITIVE_INFINITY;
        for (let i = 0; i < meteors.length; ++i) {
            if (meteors[i].inside(x, y)) {
                const d = meteors[i].distance(x, y);
                if (d < distance) {
                    distance = d;
                    index = i;
                }
            }
        }

        if (index !== -1) {
            deleteMeteor(index, counting, countingContext);
        }

        // The user clicked outside any rectangles.
    }
}

// This function creates a new Meteor object.
function Meteor(x0, y0, x1, y1, type, id) {
    const meteor = this;
    meteor.id = id;

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

    meteor.type = '';

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
        let context = canvas.getContext('2d');
        if (!context) {
            alert('Error: no canvas-context!');
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

function addListener(el, ev, listener) {
    if (el.addEventListener) {
        el.addEventListener(ev, listener, false);
    } else if (el.attachEvent) {
        el.attachEvent(`on${ev}`, listener);
    } else if (el[`on${ev.charAt(0).toUpperCase()}`]) {
        el[`on${ev.charAt(0).toUpperCase()}`] = listener;
    }
}

