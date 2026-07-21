type LogoVariant = "mark" | "horizontal" | "horizontal-white";

interface LogoProps {
  variant?: LogoVariant;
  className?: string;
  /** Altura em px; largura é proporcional. Padrão: 40 (mark) / 48 (horizontal). */
  height?: number;
}

/**
 * Logo da Carteira Financeira — "W ascendente".
 * O símbolo é o W de Wallet e também uma linha de gráfico em alta.
 *
 * - mark:              só o símbolo (navbar compacta, mobile, avatar)
 * - horizontal:        símbolo + wordmark, para header em fundo claro
 * - horizontal-white:  versão para fundo navy (login, footer)
 */
export function Logo({ variant = "mark", className, height }: LogoProps) {
  if (variant === "horizontal" || variant === "horizontal-white") {
    const onNavy = variant === "horizontal-white";
    const h = height ?? 48;
    return (
      <svg
        viewBox="0 0 320 80"
        height={h}
        className={className}
        role="img"
        aria-label="Carteira Financeira"
        xmlns="http://www.w3.org/2000/svg"
      >
        <rect
          x="8"
          y="8"
          width="64"
          height="64"
          rx="14"
          fill={onNavy ? "rgba(255,255,255,0.08)" : "#01255E"}
          stroke={onNavy ? "#FFD700" : "none"}
          strokeWidth={onNavy ? 1.5 : 0}
        />
        <polyline
          points="19,55 30,30 36,45 45,22 56,48"
          fill="none"
          stroke="#FFD700"
          strokeWidth={3.2}
          strokeLinecap="round"
          strokeLinejoin="round"
        />
        <circle cx="45" cy="22" r="3.6" fill="#FFD700" />
        <text
          x="90"
          y="42"
          fontFamily="Inter, system-ui, sans-serif"
          fontSize="26"
          fontWeight="700"
          fill={onNavy ? "#FFFFFF" : "#01255E"}
        >
          Carteira
        </text>
        <text
          x="91"
          y="62"
          fontFamily="Inter, system-ui, sans-serif"
          fontSize="11"
          fontWeight="500"
          letterSpacing="3"
          fill={onNavy ? "#FFFFFF" : "#1F1F1E"}
          fillOpacity={onNavy ? 0.6 : 0.5}
        >
          FINANCEIRA
        </text>
      </svg>
    );
  }

  const h = height ?? 40;
  return (
    <svg
      viewBox="0 0 128 128"
      height={h}
      className={className}
      role="img"
      aria-label="Carteira Financeira"
      xmlns="http://www.w3.org/2000/svg"
    >
      <rect width="128" height="128" rx="28" fill="#01255E" />
      <polyline
        points="30,94 52,44 64,74 82,32 100,84"
        fill="none"
        stroke="#FFD700"
        strokeWidth={6}
        strokeLinecap="round"
        strokeLinejoin="round"
      />
      <circle cx="82" cy="32" r="7" fill="#FFD700" />
    </svg>
  );
}

export default Logo;
