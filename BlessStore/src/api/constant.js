export const server = {
    ip:"127.0.0.1",
    port:"7001"
};

export function getBaseUrl() {
    return `//${server.ip}:${server.port}`;
}