export const server = {
    ip:"192.168.200.56",
    port:"8887"
};

export function getBaseUrl() {
    return `//${server.ip}:${server.port}`;
}