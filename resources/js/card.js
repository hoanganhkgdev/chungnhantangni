import QRCode from 'qrcode';
import { toPng } from 'html-to-image';
import JSZip from 'jszip';

// QR vẽ tại chỗ (không phụ thuộc dịch vụ ngoài, và ảnh xuất ra không bị lỗi CORS).
async function renderQr() {
    const imgs = [...document.querySelectorAll('img[data-qr]')];
    await Promise.all(imgs.map(async (img) => {
        img.src = await QRCode.toDataURL(img.dataset.qr, { width: 360, margin: 1, errorCorrectionLevel: 'M' });
        await img.decode().catch(() => {});
    }));
}

const slug = (s) => String(s).normalize('NFD').replace(/[̀-ͯ]/g, '').replace(/[^A-Za-z0-9._-]+/g, '-').replace(/^-|-$/g, '');

function download(url, name) {
    const a = document.createElement('a');
    a.href = url;
    a.download = name;
    document.body.appendChild(a);
    a.click();
    a.remove();
}

const toBlob = async (dataUrl) => (await fetch(dataUrl)).blob();

async function exportCards() {
    const root = document.getElementById('export-root');
    const status = document.getElementById('export-status');
    const again = document.getElementById('export-again');
    const groups = [...root.querySelectorAll('[data-card]')];
    const say = (t) => { status.textContent = t; };

    await document.fonts.ready;

    const files = [];
    for (const [i, g] of groups.entries()) {
        say(`Đang tạo ảnh ${i + 1}/${groups.length}…`);
        const base = slug(g.dataset.card);
        for (const [cls, suffix] of [['.card-mattruoc', 'mat-truoc'], ['.card-matsau', 'mat-sau']]) {
            const el = g.querySelector(cls);
            const png = await toPng(el, {
                pixelRatio: 2, width: 1068, height: 680, backgroundColor: '#ffffff',
                style: { margin: '0', zoom: '1' },
            });
            files.push({ name: `${base}-${suffix}.png`, blob: await toBlob(png) });
        }
    }

    const save = async () => {
        if (files.length === 2) {
            // Một thẻ: tải thẳng hai ảnh.
            for (const f of files) {
                const url = URL.createObjectURL(f.blob);
                download(url, f.name);
                await new Promise((r) => setTimeout(r, 400));
                URL.revokeObjectURL(url);
            }
            return;
        }
        const zip = new JSZip();
        files.forEach((f) => zip.file(f.name, f.blob));
        const url = URL.createObjectURL(await zip.generateAsync({ type: 'blob' }));
        download(url, `the-tang-ni-${files.length / 2}-the.zip`);
        setTimeout(() => URL.revokeObjectURL(url), 5000);
    };

    await save();
    say(files.length === 2 ? 'Đã tải 2 ảnh (mặt trước, mặt sau).' : `Đã tải file ZIP gồm ${files.length} ảnh của ${groups.length} thẻ.`);
    again.hidden = false;
    again.onclick = save;
}

async function init() {
    await renderQr();

    const root = document.getElementById('export-root');
    if (root) {
        const fit = () => { root.style.zoom = Math.min(1, (document.documentElement.clientWidth - 16) / 1068); };
        fit();
        window.addEventListener('resize', fit);
        try {
            await exportCards();
        } catch (e) {
            console.error(e);
            document.getElementById('export-status').textContent = 'Không tạo được ảnh: ' + e.message;
        }
    }
}

init();
