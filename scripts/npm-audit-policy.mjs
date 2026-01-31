#!/usr/bin/env node
import { execSync } from "node:child_process";
import { readFileSync } from "node:fs";

const allowlistPath = process.argv[2] || "scripts/npm-audit-allowlist.json";
let allowlist = { packages: [], advisories: [] };

try {
  allowlist = JSON.parse(readFileSync(allowlistPath, "utf8"));
} catch (error) {
  console.error(`Unable to read allowlist at ${allowlistPath}.`);
  console.error(error?.message || error);
  process.exit(2);
}

let output = "";
try {
  output = execSync("npm audit --json", { stdio: ["ignore", "pipe", "pipe"] }).toString();
} catch (error) {
  output = error?.stdout?.toString() || "";
  if (!output) {
    console.error("npm audit failed and produced no JSON output.");
    console.error(error?.message || error);
    process.exit(2);
  }
}

let report;
try {
  report = JSON.parse(output);
} catch (error) {
  console.error("Unable to parse npm audit JSON output.");
  console.error(error?.message || error);
  process.exit(2);
}

const vulnerabilities = report?.vulnerabilities || {};
const failing = [];

for (const [name, info] of Object.entries(vulnerabilities)) {
  const severity = info?.severity;
  if (severity !== "high" && severity !== "critical") {
    continue;
  }

  let allowed = allowlist.packages.includes(name);
  const via = Array.isArray(info?.via) ? info.via : [];

  for (const entry of via) {
    if (typeof entry === "string") {
      if (allowlist.packages.includes(entry)) {
        allowed = true;
        break;
      }
      continue;
    }

    if (entry && typeof entry === "object") {
      if (entry.url && allowlist.advisories.includes(entry.url)) {
        allowed = true;
        break;
      }
      if (entry.source && allowlist.advisories.includes(String(entry.source))) {
        allowed = true;
        break;
      }
    }
  }

  if (!allowed) {
    failing.push({
      name,
      severity,
      range: info?.range,
      via
    });
  }
}

if (failing.length > 0) {
  console.error("npm audit policy failed. Unallowlisted vulnerabilities found:");
  for (const vuln of failing) {
    const via = Array.isArray(vuln.via)
      ? vuln.via.map((item) => (typeof item === "string" ? item : item.name || item.url || item.source)).join(", ")
      : "";
    console.error(`- ${vuln.name} (${vuln.severity}) ${vuln.range || ""} ${via}`);
  }
  process.exit(1);
}

console.log("npm audit policy passed (only allowlisted high/critical issues present).");
