/**
 * Parking Finder - Zone Marker Manager using Konva.js
 */

const colors = ["#10B981", "#3B82F6", "#F59E0B", "#8B5CF6", "#EC4899"];

class ZoneMarkerApp {
    constructor(containerId, tableId) {
        this.container = document.getElementById(containerId);
        this.table = document.getElementById(tableId);
        this.stage = null;
        this.layer = null;
        this.imageLayer = null;
        this.bgImage = null;
        this.imageObj = new Image();
        this.rectangles = [];
        this.globalScale = 1;
        this.dvrConfig = null;
        this.colorIndex = 0;
    }

    init(dvrConfig) {
        this.dvrConfig = dvrConfig;
        const width = this.container.offsetWidth || 800;
        const height = Math.round(width * 0.5625); // 16:9 ratio default

        // Clear existing container content
        this.container.innerHTML = '';
        if (this.table) {
            this.table.innerHTML = `
                <tr>
                    <th style="width: 80px;">Acción</th>
                    <th>Espacio / Zona</th>
                    <th>Dimensiones (Px)</th>
                    <th>Estado</th>
                    <th style="width: 160px;">Largo (Metros)</th>
                </tr>
            `;
        }

        // Initialize Konva Stage
        this.stage = new Konva.Stage({
            container: this.container.id,
            width: width,
            height: height
        });

        this.imageLayer = new Konva.Layer();
        this.layer = new Konva.Layer();

        this.stage.add(this.imageLayer);
        this.stage.add(this.layer);

        // Deselect on empty click
        this.stage.on('click tap', (e) => {
            if (e.target === this.stage || e.target === this.bgImage) {
                this.rectangles.forEach(r => r.tr.nodes([]));
                this.layer.draw();
            }
        });

        // Load image from DVR Connector proxy
        const frameUrl = `/api/cameras/get-frame?ip=${encodeURIComponent(dvrConfig.ip)}&port=${encodeURIComponent(dvrConfig.port)}&user=${encodeURIComponent(dvrConfig.user)}&password=${encodeURIComponent(dvrConfig.password)}&channel=${encodeURIComponent(dvrConfig.channel)}&t=${Date.now()}`;

        this.imageObj.crossOrigin = "Anonymous";
        this.imageObj.src = frameUrl;
        this.imageObj.onload = () => {
            const imgWidth = this.imageObj.naturalWidth || width;
            const imgHeight = this.imageObj.naturalHeight || height;

            // Adjust stage height based on image aspect ratio
            const calculatedHeight = Math.round((width / imgWidth) * imgHeight);
            this.stage.height(calculatedHeight);

            this.bgImage = new Konva.Image({
                x: 0,
                y: 0,
                image: this.imageObj,
                width: width,
                height: calculatedHeight
            });

            this.imageLayer.destroyChildren();
            this.imageLayer.add(this.bgImage);
            this.bgImage.moveToBottom();
            this.imageLayer.draw();

            // Add first initial default zone if none exist
            if (this.rectangles.length === 0) {
                this.addZone();
            }
        };

        this.imageObj.onerror = () => {
            console.warn("Could not load camera frame, drawing fallback canvas text");
        };

        // Window resize handler
        window.addEventListener('resize', () => this.handleResize());
    }

    handleResize() {
        if (!this.stage || !this.container) return;
        const containerWidth = this.container.offsetWidth;
        if (!containerWidth) return;

        const currentWidth = this.stage.width();
        if (currentWidth <= 0) return;

        const scale = containerWidth / currentWidth;
        this.globalScale = scale;

        this.stage.width(containerWidth);
        this.stage.height(this.stage.height() * scale);
        this.stage.scale({ x: scale, y: scale });
    }

    addZone() {
        if (!this.layer) return;

        const color = colors[this.colorIndex % colors.length];
        this.colorIndex++;

        const zoneNumber = this.rectangles.length + 1;
        const rect = new Konva.Rect({
            x: 50 + (zoneNumber * 20),
            y: 50 + (zoneNumber * 20),
            width: 140,
            height: 100,
            stroke: color,
            strokeWidth: 3,
            name: 'rect',
            draggable: true
        });

        rect.strokeScaleEnabled(false);
        this.layer.add(rect);

        // Konva Transformer for resize/rotation
        const tr = new Konva.Transformer({
            nodes: [rect],
            rotateEnabled: false,
            ignoreStroke: true,
            anchorStroke: color,
            anchorFill: '#ffffff',
            anchorSize: 10
        });
        this.layer.add(tr);

        // Click to select shape
        rect.on('click tap', () => {
            this.rectangles.forEach(r => r.tr.nodes([]));
            tr.nodes([rect]);
            this.layer.draw();
        });

        // Table row for this zone
        const trRow = document.createElement("tr");
        trRow.innerHTML = `
            <td>
                <button class="btn-delete-zone" title="Eliminar Zona">
                    ✕
                </button>
            </td>
            <td class="fw-semibold">Zona #${zoneNumber}</td>
            <td class="dimensions-cell">Ancho: ${Math.round(rect.width() * rect.scaleX())}px, Alto: ${Math.round(rect.height() * rect.scaleY())}px</td>
            <td><span class="status-tag libre">Monitoreado</span></td>
            <td>
                <input type="number" step="0.5" min="0.5" max="50" value="5.0" class="meter-input form-control-sm" placeholder="Ej: 5.0">
            </td>
        `;

        const deleteBtn = trRow.querySelector('.btn-delete-zone');
        const meterInput = trRow.querySelector('.meter-input');
        const dimCell = trRow.querySelector('.dimensions-cell');

        deleteBtn.onclick = () => {
            rect.destroy();
            tr.destroy();
            trRow.remove();
            this.rectangles = this.rectangles.filter(r => r.rect !== rect);
            this.layer.draw();
        };

        rect.on('dragmove transform', () => {
            const currentW = Math.round(rect.width() * rect.scaleX());
            const currentH = Math.round(rect.height() * rect.scaleY());
            dimCell.textContent = `Ancho: ${currentW}px, Alto: ${currentH}px (X:${Math.round(rect.x())}, Y:${Math.round(rect.y())})`;
        });

        this.table.appendChild(trRow);
        this.rectangles.push({
            id: zoneNumber,
            rect: rect,
            tr: tr,
            row: trRow,
            meterInput: meterInput
        });

        this.layer.draw();
    }

    getZonesData() {
        return this.rectangles.map((item, idx) => {
            const r = item.rect;
            const x1 = Math.round(r.x());
            const y1 = Math.round(r.y());
            const width = Math.round(r.width() * r.scaleX());
            const height = Math.round(r.height() * r.scaleY());
            const x2 = x1 + width;
            const y2 = y1 + height;
            const meters = parseFloat(item.meterInput.value) || 5.0;

            return {
                zone_number: idx + 1,
                geometry: [{ x1, y1, x2, y2, width, height }],
                total_length_meters: meters,
                meters: meters
            };
        });
    }
}

// Global instance helper
window.zoneMarkerAppInstance = new ZoneMarkerApp('container', 'data_section');

window.initZoneMarkerApp = function(dvrConfig) {
    window.zoneMarkerAppInstance.init(dvrConfig);
};

window.addNewZoneSpace = function() {
    window.zoneMarkerAppInstance.addZone();
};

window.getZonesConfiguration = function() {
    return window.zoneMarkerAppInstance.getZonesData();
};
