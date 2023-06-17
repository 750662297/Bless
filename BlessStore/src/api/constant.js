export const server = {
    ip:"127.0.0.1",
    port:"8887"
};

export function getBaseUrl() {
    return `//${server.ip}:${server.port}`;
}