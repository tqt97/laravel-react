import { readFile } from 'node:fs/promises';

const css = await readFile('resources/css/app.css', 'utf8');
const scopes = [...css.matchAll(/(:root|\.dark)\s*\{([\s\S]*?)\}/g)];
const pairs = [
    ['foreground', 'background'],
    ['card-foreground', 'card'],
    ['popover-foreground', 'popover'],
    ['primary-foreground', 'primary'],
    ['secondary-foreground', 'secondary'],
    ['muted-foreground', 'muted'],
    ['accent-foreground', 'accent'],
    ['destructive-foreground', 'background'],
    ['sidebar-foreground', 'sidebar'],
    ['sidebar-primary-foreground', 'sidebar-primary'],
    ['sidebar-accent-foreground', 'sidebar-accent'],
];

function parseOklch(value) {
    const match = value.match(/oklch\(\s*([\d.]+)\s+([\d.]+)\s+([\d.]+)/);

    if (!match) {
        throw new Error(`Unsupported color value: ${value}`);
    }

    return {
        l: Number(match[1]),
        c: Number(match[2]),
        h: (Number(match[3]) * Math.PI) / 180,
    };
}

function oklchToSrgb(color) {
    const a = color.c * Math.cos(color.h);
    const b = color.c * Math.sin(color.h);
    const lmsPrime = [
        color.l + 0.3963377774 * a + 0.2158037573 * b,
        color.l - 0.1055613458 * a - 0.0638541728 * b,
        color.l - 0.0894841775 * a - 1.291485548 * b,
    ];
    const lms = lmsPrime.map((value) => value ** 3);
    const linear = [
        4.0767416621 * lms[0] - 3.3077115913 * lms[1] + 0.2309699292 * lms[2],
        -1.2684380046 * lms[0] + 2.6097574011 * lms[1] - 0.3413193965 * lms[2],
        -0.0041960863 * lms[0] - 0.7034186147 * lms[1] + 1.707614701 * lms[2],
    ];

    return linear.map((value) =>
        value <= 0.0031308
            ? 12.92 * value
            : 1.055 * Math.max(value, 0) ** (1 / 2.4) - 0.055,
    );
}

function luminance(color) {
    return oklchToSrgb(parseOklch(color))
        .map((channel) =>
            channel <= 0.03928
                ? channel / 12.92
                : ((channel + 0.055) / 1.055) ** 2.4,
        )
        .reduce(
            (sum, channel, index) =>
                sum + channel * [0.2126, 0.7152, 0.0722][index],
            0,
        );
}

function contrastRatio(foreground, background) {
    const foregroundLuminance = luminance(foreground);
    const backgroundLuminance = luminance(background);
    const lighter = Math.max(foregroundLuminance, backgroundLuminance);
    const darker = Math.min(foregroundLuminance, backgroundLuminance);

    return (lighter + 0.05) / (darker + 0.05);
}

const failures = [];

for (const [, scope, body] of scopes) {
    const variables = Object.fromEntries(
        [...body.matchAll(/--([\w-]+):\s*(oklch\([^;]+\))/g)].map(
            ([, name, value]) => [name, value],
        ),
    );

    for (const [foreground, background] of pairs) {
        if (!variables[foreground] || !variables[background]) {
            continue;
        }

        const ratio = contrastRatio(
            variables[foreground],
            variables[background],
        );

        if (ratio < 4.5) {
            failures.push(
                `${scope}: ${foreground} on ${background} = ${ratio.toFixed(2)}:1`,
            );
        }
    }
}

if (failures.length > 0) {
    console.error(
        'Accessibility contrast check failed (WCAG AA requires at least 4.5:1):',
    );
    failures.forEach((failure) => console.error(`- ${failure}`));
    process.exitCode = 1;
} else {
    console.log('Accessibility contrast check passed.');
}
