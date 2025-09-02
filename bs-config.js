module.exports = {
  proxy: "https://zen.cigno.local",
  open: false,
  notify: false,
  files: [
    "./**/*.php",
    "./assets/**/*.css",
    "./assets/**/*.js",
  ],
  watchEvents: ["change", "add", "unlink"],
  // Inietta CSS senza reload completo quando possibile
  injectChanges: true,
  // Evita cache aggressive del browser
  headers: { "Cache-Control": "no-cache, no-store, must-revalidate" }
};
